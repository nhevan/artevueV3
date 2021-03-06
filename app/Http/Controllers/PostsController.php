<?php

namespace App\Http\Controllers;

use App\Pin;
use App\Tag;
use App\Post;
use App\User;
use Exception;
use App\Artist;
use App\Gallery;
use App\Hashtag;
use App\Follower;
use Vision\Image;
use Vision\Vision;
use Vision\Feature;
use App\PostHashtag;
use GuzzleHttp\Client;
use Illuminate\Http\File;
use App\Mail\SendGalleryPdf;
use Illuminate\Http\Request;
use App\Events\NewBuyPostRequest;
use App\Traits\CounterSwissKnife;
use App\Jobs\SendDetectedHashtags;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Acme\Transformers\LikeTransformer;
use Acme\Transformers\PostTransformer;
use Illuminate\Support\Facades\Storage;
use App\Jobs\SendPostDeletedNotification;
use Illuminate\Http\Response as IlluminateResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PostsController extends ApiController
{
	use CounterSwissKnife;

    protected $post;
    protected $request;
    protected $filtered_posts;
    
    /**
     * Acme/Transformers/postTransformer
     * @var postTransformer
     */
    protected $postTransformer;

    public function __construct(Post $post, PostTransformer $postTransformer, Request $request)
    {
        $this->post = $post;
        $this->postTransformer = $postTransformer;
        $this->request = $request;
    }

    public function indexWeb()
    {
        $all_posts =  $this->post->latest()->with(['owner'])->paginate(21);

        return view('posts.index', ['posts' => $all_posts]);
    }

    /**
     * list all posts of a user
     * @return [type] [description]
     */
    public function index()
    {
		$owner_id = $this->request->owner_id ? (int)$this->request->owner_id : Auth::user()->id;
		$owner = User::find($owner_id);
        if (!$owner) {
            return $this->responseNotFound('User does not exist.');
        }

    	$posts = $owner->posts()->with('artist', 'owner', 'tags')->orderBy('id','DESC')->paginate(30);
    	return $this->respondWithPagination($posts, $this->postTransformer );
    }

    /**
     * fetches all posts that has the #arteprize2017 hashtag
     * @return paginated data
     */
    public function artePrizePosts()
    {
        $arteprize_posts = $this->post->arteprizePosts()->latest()->with('artist', 'owner', 'tags')->paginate(30);

        if ($this->request->wantsJson()) {
            return $this->respondWithPagination($arteprize_posts, $this->postTransformer );
        }

        return view('posts.index', ['posts' => $arteprize_posts]);
    }

    /**
     * returns all posts that are selected by artevue
     * @return [type] [description]
     */
    public function artevueSelectedPosts()
    {
        $selected_posts = $this->post->artevueSelectedPosts()->latest()->with('artist', 'owner', 'tags')->paginate(30);

        if ($this->request->wantsJson()) {
            return $this->respondWithPagination($selected_posts, $this->postTransformer );
        }

        return view('posts.index', ['posts' => $selected_posts]);
    }

    /**
     * returns all posts that has a price and a button to buy
     * @return [type] [description]
     */
    public function forSalePosts()
    {
        $on_sale_posts = $this->post->forSalePosts()->latest()->with('artist', 'owner', 'tags')->paginate(30);

        if ($this->request->wantsJson()) {
            return $this->respondWithPagination($on_sale_posts, $this->postTransformer );
        }

        return view('posts.index', ['posts' => $on_sale_posts]);
    }

    /**
     * returns all posts that are selected for sale by Artevue
     * @return [type] [description]
     */
    public function selectedSalePosts()
    {
        $on_sale_posts = $this->post->selectedSalePosts()->latest()->with('artist', 'owner', 'tags')->paginate(30);

        if ($this->request->wantsJson()) {
            return $this->respondWithPagination($on_sale_posts, $this->postTransformer );
        }

        return view('posts.index', ['posts' => $on_sale_posts]);
    }

    /**
     * swaps the is_undiscoverable property of a given post
     * @param  Post   $post [description]
     * @return [type]       [description]
     */
    public function swapDiscoverability(Post $post)
    {
        $post->swapDiscoverability();

        return back();
    }

    /**
     * swaps the is_selected_for_sale property of a given post
     * @param  Post   $post [description]
     * @return [type]       [description]
     */
    public function swapSaleStatus(Post $post)
    {
        $post->swapSaleStatus();

        return back();
    }

    /**
     * swaps the is_selected_by_artevue property of a post (swaps curators selection status)
     * @param  Post   $post [description]
     * @return [type]       [description]
     */
    public function swapCuratorSelectionStatus(Post $post)
    {
        $post->swapCuratorSelectionStatus();

        return back();
    }

    /**
     * Handles a new post request
     * @return [type] [description]
     */
    public function store()
    {
    	if ($this->hasValidationError()) {
    		return $this->responseValidationError();
    	}
        if($this->request->is_gallery && $this->givenInvalidGalleryIds()) {
            $this->setValidationErrors(['is_gallery' => 'The provided galleries does not exist or does not belong to the logged in user.']);
            return $this->responseValidationError();   
        }
        $this->setArtist();
        $new_post = $this->request->user()->posts()->save($this->savePost());
        $this->post = $new_post;
        $this->pinIfGalleryItem();
        $this->updateCounters();
        $this->saveTaggedUsers();
        $this->saveHashtags();
        $this->sendNewPostEvent(); //need to implement

        $this->trackAction(Auth::user(), "New Post");
        if($this->request->access_token){
            $this->postToFacebook($this->post, $this->request->access_token);
            $this->trackAction(Auth::user(), "New Post with FB share");
        }
        
        return $this->respond(['message'=>'New Post created.']);
    }

    /**
     * stores the tagged users of a given post
     * @return [type] [description]
     */
    public function saveTaggedUsers()
    {
        if(!$this->request->tagged_users){
            return;
        }

        $tags = $this->request->tagged_users;

        foreach($tags as $tag) {
            $tag['post_id'] = $this->post->id;
            $this->tagUser($tag);
        }
    }

    /**
     * tag a single user to a post
     * @param  array  $tag [description]
     * @return [type]      [description]
     */
    private function tagUser(array $tag)
    {
        $user = User::find($tag['user_id']);
        if($user){
            $tag['username'] = $user->username;
            $new_tag = Tag::create($tag);
            if($new_tag) $this->incrementUserTaggedCount($tag['user_id']);
        }
    }

    public function showWeb(Post $post)
    {
        $post->load('owner','artist', 'tags');

        return view('posts.show', compact('post'));
    }

    /**
     * returns the details of a specific post
     * @param  Post   $post [description]
     * @return [type]       [description]
     */
    public function show(Post $post)
    {
        $post->load('owner','artist', 'tags');
        
        if (Auth::check()) {
            $this->trackAction(Auth::user(), "View Post", ['Post ID' => $post->id]);
        }

        return $this->respondTransformattedModel($post->toArray(), $this->postTransformer);
    }

    /**
     * displays the post edit form to the admin
     * @param  Post   $post [description]
     * @return [type]       [description]
     */
    public function showEditForm(Post $post)
    {
        return view('posts.edit', compact('post'));
    }

    /**
     * processes the post edit request sent by admin
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function editWeb(Post $post)
    {
        $this->post = $post;
        $this->updateHashtags();
        $this->updateTaggedUsers();
        $this->post->fill($this->request->all());
        $this->post->save();

        return redirect()->route('posts.show', ['post' => $post->id]);
    }

    /**
     * edits a post model
     * @param  Post   $post [description]
     * @return [type]       [description]
     */
    public function edit(Post $post)
    {
    	$this->post = $post;
    	if (!$this->isPostOwner()) {
    		return $this->responseUnauthorized('Only a post owner can update his/her post.');
    	}
    	if($this->hadArtist()){
    		$old_artist_id  = $this->post->artist->id;
    		if(!$this->sameArtist()){
    			$this->setArtist();
    			$this->decreasePreviousArtistPostCount($old_artist_id);
    		}
    	}else{
    		$this->setArtist();
    	}
    	$this->updateHashtags();
        $this->updateTaggedUsers();
    	$this->post->fill($this->request->all());
    	$this->post->save();

        $this->trackAction(Auth::user(), "Edit Post", ['Post ID' => $post->id]);

    	return $this->respond(['message' => 'Post Successfully Updated.']);
    }

    /**
     * while editing a post it deletes all the previous tags and enters the new tags
     * @return [type] [description]
     */
    public function updateTaggedUsers()
    {
        $this->removePreviousTaggedUsers();
        $this->saveTaggedUsers();
    }

    /**
     * deletes all the current tagged users in a post
     * @return [type] [description]
     */
    public function removePreviousTaggedUsers()
    {
        foreach($this->post->tags as $tag){
            $this->decrementUserTaggedCount($tag->user_id);
            $tag->delete();
        }
    }

    /**
     * deletes a post object
     * @param  [type] $post_id [description]
     * @return [type]          [description]
     */
    public function delete($post_id)
    {
        $post = Post::find($post_id);
        if (!$post) {
            return $this->responseNotFound('Post does not exist.');
        }

        $this->post = $post;
        if ( !($this->isAdmin() || $this->isPostOwner() ))  {
            return $this->responseUnauthorized('Only the owner of the post can delete it.');
        }

        $this->decrementUserPostCount($this->post->owner_id);
        $this->decrementUserPinCountWhoPinnedThisPost($this->post->id);
        $this->decrementUserLikeCountWhoLikedThisPost($this->post->id);
        $this->decrementUserCommentCountWhoCommentedOnThisPost($this->post->id);
        $this->decrementUserTagCountWhoWereTaggedOnThisPost($this->post->id);
        
        if ($this->post->artist_id) {
            $this->decreasePreviousArtistPostCount($this->post->artist_id);
        }
        $owner = User::find($this->post->owner_id);
        dispatch(new SendPostDeletedNotification($owner));
        $this->post->delete();

        $this->trackAction(Auth::user(), "Delete Post");

        if ($this->request->wantsJson()) {
            return $this->respond(['message' => 'Post successfully deleted']);
        }

        if (strpos(url()->previous(), 'user-posts') !== false) {
            $user_id = $this->getPreviouslyVisitedUserId();

            return redirect()->route('users.posts', ['user_id' => $user_id]);
        }

        return redirect()->route('posts.index');
    }

    /**
     * returns the user id of the user that the admin was visiting
     * @return [type] [description]
     */
    public function getPreviouslyVisitedUserId()
    {
        return (int) explode("/", url()->previous())[count(explode("/", url()->previous())) - 1];
    }

    /**
     * check if the post is pinned by the current user
     * @return boolean [description]
     */
    public function isPinned()
    {
        return Pin::where('user_id', $this->request->user()->id)->where('post_id',$this->post->id)->first();
    }

    /**
     * swaps the gallery and lock status of a post
     * @param  [type] $post_id [description]
     * @return [type]          [description]
     */
    public function swapLockStatus($post_id)
    {
        $post = Post::find($post_id);
        if (!$post) {
            return $this->responseNotFound('Post does not exist.');
        }

        $this->post = $post;
        if (!$this->isPostOwner()) {
            return $this->responseUnauthorized('Only a post owner can swap its gallery presence status.');
        }

        $pin = Pin::where(['user_id'=> Auth::user()->id, 'post_id' => $this->post->id])->first();
        
        if ($pin) {
            if ($this->request->is_locked != null) {
                $pin->is_locked = $this->request->is_locked;
                $pin->save();
                return $this->respond(['message' => 'Lock status successfully swapped.']);
            }
        }

        return $this->respond(['message' => 'Nothing to update.']);
    }

    /**
     * checks for post ownership
     * @return boolean       [description]
     */
    public function isPostOwner()
    {
        	return $this->post->owner->id == $this->request->user()->id;
    }

    /**
     * checks if the current user is admin
     * @return boolean [description]
     */
    public function isAdmin()
    {
        $allowed_user_types = [1, 2]; //allow super admin and admins only

        return in_array($this->request->user()->user_type_id, $allowed_user_types);
    }

    /**
     * checks if a post belongs to a Artist or not
     * @param  Post    $post [description]
     * @return boolean       [description]
     */
    public function hadArtist()
    {
    	return $this->post->artist;
    }

    /**
     * checks of the edit post request has the same artist as the previous one in post
     * @param  Post   $post [description]
     * @return [type]       [description]
     */
    public function sameArtist()
    {
    	return $this->post->artist->title == $this->request->artist;
    }

    /**
     * check if the post is marked as gallery item, if yes then pin it
     * @return [type] [description]
     */
    public function pinIfGalleryItem()
    {
        if ($this->request->is_gallery) {
    		$this->pinPost();
    	}
    }

    private function getGalleriesFromIsGalleryField()
    {
        if(gettype($this->request->is_gallery) == 'array') 
            return $this->request->is_gallery;
        
        return $target_galleries = explode(',', str_replace(' ','',$this->request->is_gallery));
    }

    /**
     * pins a post
     * @return [type] [description]
     */
    public function pinPost()
    {
        $target_galleries = $this->getGalleriesFromIsGalleryField();
        $pin_count = $this->post->pin_count;
        
        foreach ($target_galleries as $gallery_id) {
        	Pin::create([ 'post_id' => $this->post->id, 'user_id' => $this->request->user()->id, 'gallery_id' => $gallery_id]);
            $pin_count++;
        }

        $this->post->pin_count = $pin_count;
        $this->post->save();
    }

    /**
     * sets the artist for the post
     */
    public function setArtist()
    {
    	$artist_name = $this->request->artist;
    	if($artist_name){
    		$artist = Artist::firstOrCreate(['title' => $artist_name]);
    		$artist->post_count = $artist->post_count + 1;
    		$artist->save();
    		
    		return $this->request->merge(['artist_id' => $artist->id]);
    	}
    	return $this->request->merge(['artist_id' => null]);
    }
    /**
     * checks the request object for any validation errors
     * @return boolean [description]
     */
    public function hasValidationError()
    {
        $platform = strtolower(request()->header("X-ARTEVUE-App-Platform"));
        $app_version = (float) strtolower(request()->header("X-ARTEVUE-App-Version"));

        if($app_version >= 2){
            $rules = [
                'post_image' => 'required',
                'description' => 'nullable|max:500',
                'hashtags' => 'nullable|max:250',
                'aspect_ratio' => 'nullable|numeric',
                'price' => 'nullable|numeric',
                'has_buy_btn' => 'nullable|in:0,1',
                'address' => 'nullable|max:120',
                'address_title' => 'nullable|max:120',
                'is_public' => 'nullable|in:0,1',
                'is_gallery' => 'nullable',
                'post_art_type_id' => 'nullable|numeric|min:1|max:10',
            ];
        }else{
            $rules = [
                'post_image' => 'required|file',
                'description' => 'nullable|max:250',
                'hashtags' => 'nullable|max:250',
                'aspect_ratio' => 'nullable|numeric',
                'price' => 'nullable|numeric',
                'has_buy_btn' => 'nullable|in:0,1',
                'address' => 'nullable|max:120',
                'address_title' => 'nullable|max:120',
                'is_public' => 'nullable|in:0,1',
                'is_gallery' => 'nullable|in:0,1',
            ];
        }

        return !$this->setRequest($this->request)->isValidated($rules);
    }

    /**
     * saves a post model to DB
     * @return [type] [description]
     */
    public function savePost()
    {
        $path = $this->uploadPostImageTos3();
        $this->request->merge(['image' => $path]);

    	return New Post($this->request->all());
    }

    /**
     * uploads a post image to Amazon s3
     * @return [type] [description]
     */
    public function uploadPostImageTos3()
    {
        $app_version = (float) strtolower(request()->header("X-ARTEVUE-App-Version"));

        if($app_version >= 2){
            $filepath = $this->makeFileFromBase64($this->request->post_image);

            $path = Storage::disk('s3')->putFile('img/posts', new File($filepath));
            unlink($filepath);
        }else{
        	$path = $this->request->file('post_image')->store(
                'img/posts', 's3'
            );
        }
        
        return $path;
    }

    /**
     * creates a file from a given base 64 encoded string
     * @param  [type] $image_as_base64_string [description]
     * @return [type]                         [description]
     */
    public function makeFileFromBase64($image_as_base64_string)
    {
        $encoded_image = $image_as_base64_string;
        $extension = explode('/', substr($encoded_image, 0, strpos($encoded_image, ';')))[1];

        if ($this->isAllowedExtension($extension)) {
            $base64 = explode(',', $encoded_image)[1];

            $filepath = storage_path('app/public')."/images/".uniqid().'.'.$extension;
            $decoded_image = base64_decode($base64);
            file_put_contents($filepath, $decoded_image);

            return $filepath;
        }

        throw new \Exception("only jpeg and jpg is allowed.");
    }

    public function sendNewPostEvent()
    {
    	# code...
    }

    public function updateCounters()
    {
    	$this->incrementUserPostCount($this->request->user()->id);
    	if ($this->request->is_gallery_item) {
    		$this->incrementUserPinCount($this->request->user()->id);
    		$this->incrementPostPinCount($this->post->id);
    	}
    }

    /**
     * saves all the hashtags entered in a post
     * @return [type] [description]
     */
    public function saveHashtags()
    {
    	preg_match_all('/(?<!\w)#\w+/',$this->request->description, $hashtags);
    	$hashtags = $hashtags[0];
    	$hashtags = array_unique($hashtags);

    	foreach ($hashtags as $hashtag) {
    		$hashtag = Hashtag::firstOrCreate(['hashtag' => $hashtag]);
    		$hashtag->use_count = $hashtag->use_count + 1;
    		$hashtag->save();
    		$this->savePostHashtag($hashtag->id);
    	}
    }

    /**
     * saves a post hashtag
     * @param  [type] $hashtag_id [description]
     * @return [type]             [description]
     */
    public function savePostHashtag($hashtag_id)
    {
    	return PostHashtag::create([ 'post_id' => $this->post->id, 'hashtag_id' => $hashtag_id]);
    }

    /**
     * updates the hashtags of a post
     * @return [type] [description]
     */
    public function updateHashtags()
    {
    	$this->deleteOldHashtags();
    	$this->saveHashtags();
    }

    /**
     * deletes all hashtags of a post
     * @return [type] [description]
     */
    public function deleteOldHashtags()
    {
    	PostHashtag::where(['post_id' => $this->post->id])->delete();
    }

    /**
     * fetches all the posts where the given user was tagged
     * @param  integer $user_id [description]
     * @return [type]          [description]
     */
    public function taggedPosts($user_id)
    {
        $user = User::find($user_id);
        if (!$user) {
            return $this->responseNotFound('User does not exist.');
        }

        $post_ids = $user->tags->pluck('post_id')->toArray();

        $posts = $this->post->whereIn('id', $post_ids)->with('artist', 'owner', 'tags')->paginate(30);

        return $this->respondWithPagination($posts, $this->postTransformer);
    }

    /**
     * fetches all the likes of a given post
     * @param  [type] $post_id [description]
     * @return [type]          [description]
     */
    public function postLikes($post_id)
    {
        $post = Post::find($post_id);
        if (!$post) {
            return $this->responseNotFound('Post does not exist.');
        }

        $this->post = $post;
        $likes = $this->post->likes()->with('user')->paginate(20);

        return $this->respondWithPagination($likes, new LikeTransformer);
    }

    /**
     * returns the feed post list of the current user
     * @return [type] [description]
     */
    public function feed()
    {
        if ($this->userIsGuest()) {
            $following_user_ids = $this->getAutoFollowersArray();
        }else{
            $following_user_ids = Follower::where('follower_id', $this->request->user()->id)->where('is_still_following', 1)->pluck('user_id')->toArray();
            array_push($following_user_ids, $this->request->user()->id);

            $this->trackAction(Auth::user(), "Feed View");
        }

        $feed_posts = $this->post->whereIn('owner_id', $following_user_ids)
                    ->orderBy('created_at', 'DESC')
                    ->with(
                        'owner',
                        'artist',
                        'tags'
                        )
                    ->take(200)
                    ->get()
                    ->toArray();
        $feed_posts = $this->getPaginated($feed_posts, 20);

        return $this->respondWithPagination($feed_posts, $this->postTransformer);
    }

    /**
     * advance search for posts using artist, keyword and date-range
     * @return [type] [description]
     */
    public function advanceSearch()
    {
        $this->filtered_posts = $this->post;
        if ($this->request->artist) {
            $this->filtered_posts = $this->searchByArtist();
        }
        if ($this->request->keyword) {
            $this->filtered_posts = $this->searchByKeyword();
        }
        if ($this->request->date_range) {
            $this->filtered_posts = $this->searchByDateRange();
        }

        $posts = $this->fetchAllFilteredPosts();

        if (!$this->userIsGuest()) {
            $this->trackAction(Auth::user(), "Advance Search");
        }
        return $this->respondWithPagination($posts, $this->postTransformer);
    }

    /**
     * returns all the filtered posts in paginated format
     * @return [type] [description]
     */
    protected function fetchAllFilteredPosts()
    {
        return $this->filtered_posts->with('owner', 'artist', 'tags')->latest()->paginate(20);
    }

    /**
     * return posts that matches the artists name
     * @return [type] [description]
     */
    protected function searchByArtist()
    {
        $artist_name = $this->request->artist;
        $artist_ids = Artist::where('title', 'like', '%'.$artist_name.'%')->pluck('id');

        $posts = $this->filtered_posts->whereIn('artist_id', $artist_ids);

        return $posts;
    }

    /**
     * returns posts whose description contains the given keyword
     * @return [type] [description]
     */
    protected function searchByKeyword()
    {
        return $this->filtered_posts->where('description', 'like', '%'.$this->request->keyword.'%');
    }

    /**
     * returns posts created within the given date range
     * @return [type] [description]
     */
    protected function searchByDateRange()
    {
        $period = $this->getPeriodFromDateRange($this->request->date_range);
        return $this->filtered_posts->where('created_at', '>=', new \DateTime($period));
    }

    /**
     * returns actual time period from a given date_range integer 
     * @param  integer $date_range [description]
     * @return [type]             [description]
     */
    protected function getPeriodFromDateRange($date_range)
    {
        $period = '';
        switch ($date_range) {
            case 0:
                $period = '-3 years';
                break;
            case 1:
                $period = '-1 month';
                break;
            case 3:
                $period = '-3 months';
                break;
            case 6:
                $period = '-6 months';
                break;
            case 12:
                $period = '-1 year';
                break;
        }
        return $period;
    }

    /**
     * emails a genrated pdf with the given posts and description
     * @return [type] [description]
     */
    public function emailGalleryPdf()
    {
        $data['gallery_name'] = $this->request->user()->metadata->gallery_name;
        $data['gallery_description'] = $this->request->user()->metadata->gallery_description;
        $data['posts'] = $this->request->posts;

        $gallery_pdf = (new SendGalleryPdf($data, Auth::user()))->onQueue('high');
        Mail::to(Auth::user()->email)->queue($gallery_pdf);
        $this->trackAction(Auth::user(), "Request Gallery PDF");

        return $this->respond(['message' => 'Requested pdf will be emailed to you shortly.']);
    }

    /**
     * returns gallery posts of a user
     * @param  [type] $user_id [description]
     * @return [type]          [description]
     */
    public function getGallery($user_id)
    {
        $limit = 30;
        $user = User::find($user_id);
        if (!$user) {
            return $this->responseNotFound('User does not exist.');
        }
        
        $pinned_posts = $this->getPinnedPosts($user_id);

        $sorted_posts = $pinned_posts->sortBy('sequence')->values()->all();

        $paginated_result = $this->getPaginated($sorted_posts, $limit);

        if (!$this->userIsGuest()) {
            $this->trackAction(Auth::user(), "View Gallery", ['Gallery Owner Id' => $user->id]);
        }

        return $this->respondWithPagination($paginated_result, $this->postTransformer);
    }

    /**
     * returns posts of a user that are marked as gallery item
     * @param  [type] $user_id [description]
     * @return [type]          [description]
     */
    protected function getGalleryPosts($user_id)
    {
        $gallery_posts = Post::where('owner_id', $user_id)->where('is_gallery_item', 1)->with('owner', 'artist', 'tags');
        if(!$this->userIsGuest() && Auth::user()->id != $user_id){
            $gallery_posts = $gallery_posts->where('is_locked', 0);
        }

        return $gallery_posts->get();
    }

    /**
     * returns pinned posts of a user
     * @param  [type] $user_id [description]
     * @return [type]          [description]
     */
    protected function getPinnedPosts($user_id)
    {
        $my_posts_ids = Post::where('owner_id', $user_id)->pluck('id')->toArray();

        $pinned_posts_ids = Pin::where('user_id', $user_id)->orderBy('post_id')->pluck('post_id');

        $pinned_sequence = Pin::where('user_id', $user_id)->orderBy('post_id')->pluck('sequence');
        $reversed_pin_sequence = $pinned_sequence->reverse();
        
        $pinned_posts = Post::whereIn('id', $pinned_posts_ids)->with('owner', 'artist', 'tags')->get();

        $sequencial_posts = $pinned_posts->transform(function ($pin, $key) use ($reversed_pin_sequence) {
            $pin['sequence'] = $reversed_pin_sequence->pop();
            return $pin;
        });

        return $sequencial_posts;
    }

    /**
     * arranges a users gallery posts
     * @return [type] [description]
     */
    public function arrangeGalleryPosts()
    {
        $count = 1;
        foreach ($this->request->posts as $post) {
            $pin = Pin::where('post_id', $post['id'])->where('user_id', Auth::user()->id)->first();
            $pin->sequence = $count;
            $pin->save();

            $count++;
        }
        return $this->respond(['message' => 'Gallery successfully arranged.']);
    }

    /**
     * fetches suggested hashtags for a given post
     * @return [type] [description]
     */
    public function fetchSuggestedHashtags()
    {
        $rules = [
            'image' => 'required',
            'key' => 'required'
        ];
        if (!$this->setRequest($this->request)->isValidated($rules)) {
            return $this->responseValidationError();
        }

        try {
            $filepath = $this->makeFileFromBase64($this->request->image);
        } catch (Exception $e) {
            return $this->setStatusCode(IlluminateResponse::HTTP_BAD_REQUEST)->respondWithError($e->getMessage());
        }

        $unique_key = $this->request->key;
        SendDetectedHashtags::dispatch(Auth::user(), $filepath, $unique_key);

        return response()->json(["message" => "Image successfully uploaded for automatic hashtag detection."]);
    }

    /**
     * checks if the extension is a allowed extension
     * @param  [type]  $extension [description]
     * @return boolean            [description]
     */
    public function isAllowedExtension($extension)
    {
        return in_array($extension, ['jpg', 'jpeg', 'png']);
    }

    public function buy(Post $post)
    {
        $interested_user = $this->request->user();
        event(new NewBuyPostRequest($interested_user, $post));
        $this->trackAction(Auth::user(), "New Buy Post Request");

        return $this->respond(["message" => 'Post buy notification successgully sent.']);
    }

    /**
     * checks if the provided galleries exists and belongs to the logged in user
     * @return [type] [description]
     */
    private function givenInvalidGalleryIds()
    {
        $target_galleries = $this->getGalleriesFromIsGalleryField();

        foreach ($target_galleries as $gallery_id) {
            if (!$this->isCurrentUserGalleryOwner($gallery_id)) 
                return true;
        }
    }

    /**
     * returns true if the logged in user is the owner of the giver gallery
     * @param  [type]  $gallery_id [description]
     * @return boolean             [description]
     */
    private function isCurrentUserGalleryOwner($gallery_id)
    {
        return !! Gallery::where('id', $gallery_id)->where('user_id', Auth::user()->id)->first();
    }

    /**
     * posts a post to facebook
     * @param  [type] $post         [description]
     * @param  [type] $access_token [description]
     * @return [type]               [description]
     */
    public function postToFacebook($post, $access_token, $privacy_value = 'EVERYONE')
    {
        $client = new Client();

        try {
            $response = $client->request("POST", "https://graph.facebook.com/v2.10/me/feed", [
                'form_params' => [
                    'access_token' => $access_token,
                    'link' => 'http://dy01r176shqrv.cloudfront.net/'.$post->image,
                    'message' => $post->description,
                    'picture' => 'http://dy01r176shqrv.cloudfront.net/'.$post->image,
                    'privacy' => [
                        'value' => $privacy_value
                    ]
                ]
            ]);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getMessage();

            return $response;
        }

        return 0;
    }
}

<?php

namespace App\Http\Controllers;

use App\Pin;
use App\Tag;
use App\Post;
use App\User;
use Exception;
use App\Artist;
use App\Hashtag;
use App\Follower;
use App\PostHashtag;
use App\Mail\SendGalleryPdf;
use Illuminate\Http\Request;
use App\Traits\CounterSwissKnife;
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
        $all_posts =  $this->post->latest()->with(['owner'])->paginate(20);

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
     * Handles a new post request
     * @return [type] [description]
     */
    public function store()
    {
    	if ($this->hasValidationError()) {
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

        $tags = $this->parseTaggedUsersInput();

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
    public function tagUser(array $tag)
    {
        $user = User::find($tag['user_id']);
        if($user){
            $tag['username'] = $user->username;
            $new_tag = Tag::create($tag);
            if($new_tag) $this->incrementUserTaggedCount($tag['user_id']);
        }
    }

    /**
     * parses the tagged_users string to array
     * @return array [description]
     */
    public function parseTaggedUsersInput()
    {
        $raw_tags = stripcslashes($this->request->tagged_users);
        $tags = json_decode($raw_tags, JSON_UNESCAPED_UNICODE);

        return $tags;
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

        $this->trackAction(Auth::user(), "View Post", ['Post ID' => $post->id]);

        return $this->respondTransformattedModel($post->toArray(), $this->postTransformer);
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

        return redirect()->route('posts.index');

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
    public function swapGalleryAndLockStatus($post_id)
    {
        $post = Post::find($post_id);
        if (!$post) {
            return $this->responseNotFound('Post does not exist.');
        }

        $this->post = $post;
        if (!$this->isPostOwner()) {
            return $this->responseUnauthorized('Only a post owner can swap its gallery presence status.');
        }

        if ($this->request->is_gallery_item != null) {
            $post->is_gallery_item = $this->request->is_gallery_item;
            $post->save();
            return $this->respond(['message' => 'Gallery status successfully swapped.']);
        }
        if ($this->request->is_locked != null) {
            $post->is_locked = $this->request->is_locked;
            $post->save();
            return $this->respond(['message' => 'Lock status successfully swapped.']);
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
    	if ($this->request->is_gallery_item) {
    		$this->pinPost();
    	}
    }

    /**
     * pins a post
     * @return [type] [description]
     */
    public function pinPost()
    {
    	return Pin::create([ 'post_id' => $this->post->id, 'user_id' => $this->request->user()->id]);
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
        $storage = config('app.storage');
    	$path = $this->request->file('post_image')->store(
            'img/posts', 's3'
        );
        
        return $path;
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
        $following_user_ids = Follower::where('follower_id', $this->request->user()->id)->where('is_still_following', 1)->pluck('user_id')->toArray();
        array_push($following_user_ids, $this->request->user()->id);

        $feed_posts = $this->post->whereIn('owner_id', $following_user_ids)->orderBy('created_at', 'DESC')->with('owner', 'artist', 'tags')->take(200)->get()->toArray();

        $feed_posts = $this->getPaginated($feed_posts, 20);

        $this->trackAction(Auth::user(), "Feed View");

        return $this->respondWithPagination($feed_posts, $this->postTransformer);
        var_dump($feed_posts);
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

        $this->trackAction(Auth::user(), "Advance Search");

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

        Mail::to(Auth::user()->email)->queue(new SendGalleryPdf($data, Auth::user()));
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
        $gallery_posts = $this->getGalleryPosts($user_id);
        $pinned_posts = $this->getPinnedPosts($user_id);

        $all_posts = $gallery_posts->merge($pinned_posts);
        $all_posts = $all_posts->sortBy('sequence')->values()->all();

        $paginated_result = $this->getPaginated($all_posts, $limit);

        $this->trackAction(Auth::user(), "View Gallery", ['Gallery Owner Id' => $user->id]);

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
        if(Auth::user()->id != $user_id){
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

        $pinned_posts_ids = Pin::where('user_id', $user_id)->whereNotIn('post_id', $my_posts_ids)->orderBy('post_id')->pluck('post_id');

        $pinned_sequence = Pin::where('user_id', $user_id)->whereNotIn('post_id', $my_posts_ids)->orderBy('post_id')->pluck('sequence');
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
            if($post['owner']['id'] == Auth::user()->id){ //post owner
                $post = $this->post->find($post['id']);
                $post->sequence = $count;
                $post->save();
            }else{ //pinned post
                $pin = Pin::where('post_id', $post['id'])->where('user_id', Auth::user()->id)->first();
                $pin->sequence = $count;
                $pin->save();
            }
            $count++;
        }
        return $this->respond(['message' => 'Gallery successfully arranged.']);
    }
}

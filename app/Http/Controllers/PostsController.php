<?php

namespace App\Http\Controllers;

use App\Pin;
use App\Post;
use App\User;
use Exception;
use App\Artist;
use App\Hashtag;
use App\PostHashtag;
use Illuminate\Http\Request;
use App\Traits\CounterSwissKnife;
use Illuminate\Support\Facades\Auth;
use Acme\Transformers\PostTransformer;
use Illuminate\Http\Response as IlluminateResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PostsController extends ApiController
{
	use CounterSwissKnife;

    protected $post;
    protected $request;
    
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

    	$posts = $owner->posts()->with('artist', 'owner')->orderBy('id','DESC')->paginate(20);
    	return $this->respondWithPagination($posts, $this->postTransformer );
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
        $this->updateCounters(); //need to implement
        $this->saveHashtags();
        $this->sendNewPostEvent(); //need to implement
        
        return $this->respond(['message'=>'New Post created.']);
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
    	$this->post->fill($this->request->all());
    	$this->post->save();

    	return $this->respond(['message' => 'Post Successfully Updated.']);
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
        if (!$this->isPostOwner()) {
            return $this->responseUnauthorized('Only the owner of the post can delete it.');
        }

        $this->decrementUserPostCount($this->post->owner_id);
        
        //decrease user like count 
        //decrease user tag count 
        //decrease pin count for all users who pinned this post
        $this->decrementUserPinCountWhoPinnedThisPost($this->post->id);
        //decrease like count for all users who liked this post
        //decrease comment count for all users who commented on this post
        //decrease tag count for all users who were tagged in this post
        
        if ($this->post->artist_id) {
            $this->decreasePreviousArtistPostCount($this->post->artist_id);
        }

        $this->post->delete();

        return $this->respond(['message' => 'Post successfully deleted']);
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
    	$path = $this->request->file('post_image')->store(
            'img/posts', 'local'
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
    	//tagged user count
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
}

<?php

namespace App\Http\Controllers;

use App\Pin;
use App\Post;
use App\User;
use App\Gallery;
use Illuminate\Http\Request;
use App\Traits\CounterSwissKnife;
use Illuminate\Support\Facades\Auth;
use Acme\Transformers\PostTransformer;
use Illuminate\Http\Response as IlluminateResponse;

class PinsController extends ApiController
{
	use CounterSwissKnife;

	protected $request;
	protected $pin;

	public function __construct(Pin $pin, Request $request)
	{
		$this->request = $request;
		$this->pin = $pin;
	}

    /**
     * pins a post to a given gallery (version 3)
     * @param  Post   $post [description]
     * @return [type]       [description]
     */
    public function store($gallery_id, $post_id)
    {
        $gallery = Gallery::find($gallery_id);
        if(!$gallery){
            return $this->responseNotFound('Gallery Not found.');
        }

        $post = Post::find($post_id);
        if (!$post) {
            return $this->responseNotFound('Post does not exist.');
        }

        if($gallery->user_id != Auth::user()->id){
            return $this->responseUnauthorized('The given gallery id does not belong to the current user.');
        }

        $is_existing = $this->pin->where([ 'post_id' => $post_id, 'gallery_id'=> $gallery_id , 'user_id' => $this->request->user()->id ])->first();

        if ($is_existing) {
            return $this->setStatusCode(IlluminateResponse::HTTP_UNPROCESSABLE_ENTITY)->respondWithError('This user has already pinned the post on the given gallery.');
        }

        $this->pin->create([ 'post_id' => $post_id, 'gallery_id'=> $gallery_id , 'user_id' => $this->request->user()->id ]);

        $this->incrementPostPinCount($post_id);
        $this->incrementUserPinCount($this->request->user()->id);
        $this->updatePinCountInFollowersTable($post->owner_id);

        $this->trackAction(Auth::user(), "New Pin", ['Post ID' => $post_id]);

        return $this->respond([ 'message' => 'Post successfully pinned.' ]);
    }

	/**
	 * pins a post (version 2)
	 * @param  Post   $post [description]
	 * @return [type]       [description]
	 */
    public function storeOld($post_id)
    {
    	$post = Post::find($post_id);
        if (!$post) {
            return $this->responseNotFound('Post does not exist.');
        }
    	$is_existing = $this->pin->where([ 'post_id' => $post_id, 'user_id' => $this->request->user()->id ])->first();
    	if (!$is_existing) {
    		$this->pin->create([ 'post_id' => $post_id, 'user_id' => $this->request->user()->id ]);

            // if ($this->isPostOwner($post)) {
            //     $post->is_gallery_item = 1;
            //     $post->save();
            // }

    		$this->incrementPostPinCount($post_id);
	    	$this->incrementUserPinCount($this->request->user()->id);
            $this->updatePinCountInFollowersTable($post->owner_id);

            $this->trackAction(Auth::user(), "New Pin", ['Post ID' => $post_id]);

    		return $this->respond([ 'message' => 'Post successfully pinned.' ]);
    	}
    	return $this->setStatusCode(IlluminateResponse::HTTP_UNPROCESSABLE_ENTITY)->respondWithError('This user already pinned this post.');
    }

    /**
     * removes a pin
     * @param  Post   $post [description]
     * @return [type]       [description]
     */
    public function delete($post_id)
    {
    	$post = Post::find($post_id);
        if (!$post) {
            return $this->responseNotFound('Post does not exist.');
        }
        
    	$is_existing = $this->pin->where([ 'post_id' => $post_id, 'user_id' => $this->request->user()->id ])->first();
    	if (!$is_existing) {
    		return $this->setStatusCode(IlluminateResponse::HTTP_NOT_FOUND)->respondWithError('This user have not pinned this post yet.');
    	}
    	$is_existing->delete();
        
        // if ($this->isPostOwner($post)) {
        //     $post->is_gallery_item = 0;
        //     $post->sequence = 0;
        //     $post->save();
        // }

    	$this->decrementPostPinCount($post_id);
    	$this->decrementUserPinCount($this->request->user()->id);

        $this->trackAction(Auth::user(), "Remove Pin", ['Post ID' => $post_id]);

		return $this->respond([ 'message' => 'Post successfully unpinned.' ]);
    }

    /**
     * fetch all pinned posts of a user
     * @param  [type] $user_id [description]
     * @return [type]          [description]
     */
    public function pinnedPosts($user_id)
    {
        $user = User::find($user_id);
        if (!$user) {
            return $this->responseNotFound('User does not exist.');
        }

        $post_ids = $user->pins->pluck('post_id');

        $posts = Post::whereIn('id', $post_ids)->with('artist', 'owner', 'tags')->latest()->paginate(20);

        return $this->respondWithPagination($posts, New PostTransformer);
    }

    /**
     * checks for post ownership
     * @return boolean       [description]
     */
    public function isPostOwner(Post $post)
    {
            return $post->owner->id == $this->request->user()->id;
    }
}

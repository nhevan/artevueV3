<?php

namespace App\Http\Controllers;

use App\Like;
use App\Post;
use Illuminate\Http\Request;
use App\Traits\CounterSwissKnife;
use Illuminate\Support\Facades\Auth;
use App\Jobs\SendNewLikeNotification;
use App\Traits\NotificationSwissKnife;
use Illuminate\Http\Response as IlluminateResponse;

class LikesController extends ApiController
{
    use CounterSwissKnife, NotificationSwissKnife;

	protected $request;
	protected $like;

	public function __construct(Like $like, Request $request)
	{
		$this->request = $request;
		$this->like = $like;
	}

	/**
	 * Likes a post
	 * @param  Post   $post [description]
	 * @return [type]       [description]
	 */
    public function store($post_id)
    {
    	$post = Post::find($post_id);
        if (!$post) {
            return $this->responseNotFound('Post does not exist.');
        }
    	$is_existing = $this->like->where([ 'post_id' => $post_id, 'user_id' => $this->request->user()->id ])->first();
    	if (!$is_existing) {
    		$new_like = $this->like->create([ 'post_id' => $post_id, 'user_id' => $this->request->user()->id ]);

            dispatch(new SendNewLikeNotification($new_like));
            $this->trackAction(Auth::user(), "New Like", ['Post ID' => $post_id]);

    		$this->incrementPostLikeCount($post_id);
	    	$this->incrementUserLikeCount($this->request->user()->id);
            $this->updatePinCountInFollowersTable($post->owner_id);

    		return $this->respond([ 'message' => 'Post successfully liked.' ]);
    	}
    	return $this->setStatusCode(IlluminateResponse::HTTP_UNPROCESSABLE_ENTITY)->respondWithError('This user already liked this post.');
    }

    /**
     * unlikes a post
     * @param  Post   $post [description]
     * @return [type]       [description]
     */
    public function delete($post_id)
    {
    	$post = Post::find($post_id);
        if (!$post) {
            return $this->responseNotFound('Post does not exist.');
        }
        
    	$is_existing = $this->like->where([ 'post_id' => $post_id, 'user_id' => $this->request->user()->id ])->first();
    	if (!$is_existing) {
    		return $this->setStatusCode(IlluminateResponse::HTTP_UNPROCESSABLE_ENTITY)->respondWithError('This user have not liked this post yet.');
    	}
    	$is_existing->delete();

    	$this->decrementPostLikeCount($post_id);
    	$this->decrementUserLikeCount($this->request->user()->id);
    	$this->decrementLikeCountInFollowersTable($post->owner_id);

        $this->trackAction(Auth::user(), "Remove Like", ['Post ID' => $post_id]);

		return $this->respond([ 'message' => 'Post successfully unliked.' ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Pin;
use App\Post;
use Illuminate\Http\Request;
use App\Traits\CounterSwissKnife;
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
	 * pins a post
	 * @param  Post   $post [description]
	 * @return [type]       [description]
	 */
    public function store($post_id)
    {
    	$post = Post::find($post_id);
        if (!$post) {
            return $this->responseNotFound('Post does not exist.');
        }
    	$is_existing = $this->pin->where([ 'post_id' => $post_id, 'user_id' => $this->request->user()->id ])->first();
    	if (!$is_existing) {
    		$this->pin->create([ 'post_id' => $post_id, 'user_id' => $this->request->user()->id ]);

    		$this->incrementPostPinCount($post_id);
	    	$this->incrementUserPinCount($this->request->user()->id);
            $this->updatePinCountInFollowersTable($post->owner_id);

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
    		return $this->setStatusCode(IlluminateResponse::HTTP_UNPROCESSABLE_ENTITY)->respondWithError('This user have not pinned this post yet.');
    	}
    	$is_existing->delete();

    	$this->decrementPostPinCount($post_id);
    	$this->decrementUserPinCount($this->request->user()->id);

		return $this->respond([ 'message' => 'Post successfully unpinned.' ]);
    }
}

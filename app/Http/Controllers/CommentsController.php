<?php

namespace App\Http\Controllers;

use App\Post;
use App\Comment;
use Illuminate\Http\Request;
use App\Traits\CounterSwissKnife;
use Acme\Transformers\CommentTransformer;

class CommentsController extends ApiController
{
    use CounterSwissKnife;

	protected $request;
	protected $comment;

	public function __construct(Comment $comment, Request $request)
	{
		$this->request = $request;
		$this->comment = $comment;
	}

	/**
	 * fetches paginated comments for the given post
	 * @param  [type] $post_id [description]
	 * @return [type]          [description]
	 */
	public function index($post_id)
	{
		$post = Post::find($post_id);
        if (!$post) {
            return $this->responseNotFound('Post does not exist.');
        }
        $comments = $this->comment->where('post_id', $post->id)->latest()->with('commentor')->paginate(15);

        return $this->respondWithPagination($comments, new CommentTransformer);
	}
	/**
	 * comment on a post
	 * @param  Post   $post [description]
	 * @return [type]       [description]
	 */
    public function store($post_id)
    {
    	$post = Post::find($post_id);
        if (!$post) {
            return $this->responseNotFound('Post does not exist.');
        }
    	$rules = [
            'comment' => 'required|max:250',
        ];
        if (!$this->setRequest($this->request)->isValidated($rules)) {
            return $this->responseValidationError();
        }

        $new_comment = $this->comment->create(['user_id'=>$this->request->user()->id, 'post_id'=>$post->id, 'comment'=>$this->request->comment]);

        if($new_comment){
        	//update counters
        	//send FCM + pusher
        	return $this->respond(['message' => 'comment successfully posted.']);
        }
    }

    /**
     * deletes a given comment
     * @param  [type] $comment_id [description]
     * @return [type]             [description]
     */
    public function delete($comment_id)
    {
    	$comment = $this->comment->find($comment_id);
        if (!$comment) {
            return $this->responseNotFound('Comment does not exist.');
        }
        $this->comment = $comment;
        
        if (!$this->isCommentOwner()) {
    		return $this->responseUnauthorized('Only the comment owner can delete the comment.');
    	}

    	$is_deleted = $this->comment->delete();
    	if ($is_deleted) {
    		//update counters
    		return $this->respond(['message' => 'The comment has been successfully deleted.']);
    	}
    }

     /**
     * checks for comment ownership
     * @return boolean       [description]
     */
    public function isCommentOwner()
    {
    	return $this->comment->user_id == $this->request->user()->id;
    }
}

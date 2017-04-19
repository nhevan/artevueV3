<?php

namespace App\Http\Controllers;

use App\Post;
use App\Comment;
use App\Hashtag;
use App\CommentHashtag;
use Illuminate\Http\Request;
use App\Traits\CounterSwissKnife;
use Illuminate\Support\Facades\Auth;
use App\Jobs\SendNewCommentNotification;
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
        $comments = $this->comment->where('post_id', $post->id)->with('commentor')->paginate(15);

        $this->trackAction(Auth::user(), "View Comments", ['Post ID' => $post_id]);

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
        $this->comment = $new_comment;
        $this->manageHashtags();
        if($new_comment){
        	$this->incrementPostCommentCount($post_id);
	    	$this->incrementUserCommentCount($this->request->user()->id);
            $this->incrementCommentCountInFollowersTable($post->owner_id);
            
            dispatch(new SendNewCommentNotification($new_comment));
            $this->trackAction(Auth::user(), "New Comment");

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
        $post_id = $comment->post_id;
        if (!$this->isCommentOwner()) {
    		return $this->responseUnauthorized('Only the comment owner can delete the comment.');
    	}

    	$is_deleted = $this->comment->delete();
    	if ($is_deleted) {
    		$this->decrementPostCommentCount($post_id);
	    	$this->decrementUserCommentCount($this->request->user()->id);
            // $this->incrementCommentCountInFollowersTable($post->owner_id);
            
            $this->trackAction(Auth::user(), "Delete Comment", ['Post ID' => $post_id]); 
            
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

    /**
     * manages the hashtags given within a comment
     * @return [type] [description]
     */
    public function manageHashtags()
    {
    	preg_match_all('/(?<!\w)#\w+/',$this->request->comment, $hashtags);
    	$hashtags = $hashtags[0];
    	$hashtags = array_unique($hashtags);

    	foreach ($hashtags as $hashtag) {
    		$hashtag = Hashtag::firstOrCreate(['hashtag' => $hashtag]);
    		$hashtag->use_count = $hashtag->use_count + 1;
    		$hashtag->save();
    		$this->saveCommentHashtag($hashtag->id);
    	}
    }

    /**
     * saves a hashtag against a comment
     * @param  [type] $hashtag_id [description]
     * @return [type]             [description]
     */
    public function saveCommentHashtag($hashtag_id)
    {
    	return CommentHashtag::create([ 'comment_id' => $this->comment->id, 'hashtag_id' => $hashtag_id]);
    }
}

<?php

namespace App\Http\Controllers;

use App\User;
use App\Message;
use App\Events\MessageSent;
use Illuminate\Http\Request;
use App\Traits\CounterSwissKnife;
use App\Traits\NotificationSwissKnife;
use Illuminate\Database\QueryException;
use Illuminate\Http\Response as IlluminateResponse;

class MessagesController extends ApiController
{
	use NotificationSwissKnife, CounterSwissKnife;

	protected $message;
	protected $request;

	public function __construct(Message $message, Request $request)
	{
		$this->message = $message;
		$this->request = $request;
	}

	/**
	 * fetches all the messages between the authenticated user and the given user (friend)
	 * @param  User    $user    [description]
	 * @param  Request $request [description]
	 * @return [type]           [description]
	 */
	public function index($friend_id)
	{
		$friend = User::find($friend_id);
		if(!$friend){
			return $this->responseNotFound('User does not exist.');
		}
		$limit = 2;
        if((int)$this->request->limit >= $limit) $limit = (int)$this->request->limit ?: $limit;

		$conversation = $this->findPaginatedConversation($friend_id, $limit);
		$conversation_array = $conversation->toArray();

		$was_any_message_marked_read = $this->markFetchedMessagesAsRead($conversation_array);

		if ($was_any_message_marked_read && $this->isAllMessagesRead($friend_id)) {
			$this->notifyFriend($friend_id);
		}
		
		return $this->respond($conversation);
	}

	/**
	 * returns true all messages from a friend has been read
	 * @param  integer  $friend_id [description]
	 * @return boolean            [description]
	 */
	public function isAllMessagesRead($friend_id)
	{
		$unread_messages = $this->message->where('is_read', 0)->where('sender_id',$friend_id)->where('receiver_id', $this->request->user()->id)->get();
		
		if (!sizeof($unread_messages->toArray())) {
			return true;
		}
	}

	/**
	 * notifies the friend that the user has read all his/her messages
	 * @param  integer $friend_id [description]
	 * @return [type]            [description]
	 */
	public function notifyFriend($friend_id)
	{
		$data = [ 'message' => 'All your messages has been read by your friend '.$this->request->user()->name, 'reader_id' => $this->request->user()->id ];
		$this->sendPusherNotification($friend_id.'-message-read-channel','message-read', $data);
	}

	/**
	 * Fetches paginated conversation between a friend
	 * @param  integer $friend_id [description]
	 * @param  integer $limit     [description]
	 * @return [type]            [description]
	 */
	protected function findPaginatedConversation($friend_id, $limit)
	{
		$participant_ids = [ $friend_id, $this->request->user()->id ];
		$conversation = $this->message->whereIn('sender_id', $participant_ids)->whereIn('receiver_id', $participant_ids)->orderBy('id', 'DESC')->paginate($limit);

		return $conversation;
	}

	/**
	 * Sets all the given messages is_read to 1
	 * @param  array  $conversation [description]
	 * @return integer tells us if any of the messages was marked read
	 */
	protected function markFetchedMessagesAsRead(array $conversation)
	{
		$was_any_message_marked_read = false;
		foreach ($conversation['data'] as $message) {
			if ($message['is_read'] == 0) {
				$this->markMessageAsRead($message['id']);
				$was_any_message_marked_read = true;
			}
		}

		return $was_any_message_marked_read;
	}

	/**
	 * marks a given message as read [is_read = 1]
	 * @param  Message $message [description]
	 * @return [type]           [description]
	 */
	protected function markMessageAsRead($message_id)
	{
		$message = Message::find($message_id);
		$message->is_read = 1;

		$message->save();
	}

	/**
	 * sends a message to a user
	 * @param  Request $request [description]
	 * @return [type]           [description]
	 */
    public function store(Request $request)
    {
    	$rules = [
            'receiver_id' => 'required',
            'message' => 'required'
        ];
        if (!$this->setRequest($request)->isValidated($rules)) {
            return $this->responseValidationError();
        }
        if ($request->is_file == 1 && $request->is_post == 1) {
    		return $this->respondWithError(['message'=>'A message can not be of both file and post type at the same time, Check your is_file and is_post key.']);
    	}
    	
    	try {
    		$message = $this->createMessage($request);
    	} catch (QueryException $e) {
    		return $this->setStatusCode(IlluminateResponse::HTTP_UNPROCESSABLE_ENTITY)->respondWithError('Something went wrong, probably the receiver does not exist.');
    	}
    	
    	event(new MessageSent($message));
    	$this->incrementMessageCount($request->user()->id);
    	$this->updateParticipantsTable($request->user()->id, $request->receiver_id, $message->id);

        return $this->respond(['message'=>'Message successfully sent.']);
    }

    /**
     * create new message instance
     * @param  Request $request 
     * @return response           
     */
    public function createMessage(Request $request)
    {
    	$message = new Message;
    	$message->sender_id = $request->user()->id;
    	$message->receiver_id = $request->receiver_id;
    	$message->message = $request->message;

    	if ($request->is_file) {
    		$message->is_file = 1;
    		$message->url = $request->file('url')->store(
	            'img/messages', 's3'
	        );
    	}
    	if ($request->is_post) {
    		$message->is_post = 1;
    		$message->url = $request->url;
    	}

		$message->save();
		return $message;
    }
}

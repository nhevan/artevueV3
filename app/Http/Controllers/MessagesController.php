<?php

namespace App\Http\Controllers;

use App\User;
use App\Message;
use App\Events\MessageSent;
use App\MessageParticipant;
use Illuminate\Http\Request;
use App\Traits\CounterSwissKnife;
use App\Traits\FileUploadSwissKnife;
use Illuminate\Support\Facades\Auth;
use App\Traits\NotificationSwissKnife;
use Illuminate\Database\QueryException;
use App\Jobs\SendNewMessageNotification;
use Acme\Transformers\MessageTransformer;
use App\Jobs\SendMessagesReadNotification;
use Illuminate\Http\Response as IlluminateResponse;

class MessagesController extends ApiController
{
	use NotificationSwissKnife, CounterSwissKnife, FileUploadSwissKnife;

	protected $message;
	protected $request;
	protected $messageTransformer;

	public function __construct(Message $message, Request $request, MessageTransformer $messageTransformer)
	{
		$this->message = $message;
		$this->request = $request;
		$this->messageTransformer = $messageTransformer;
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
		$limit = 15;
        if((int)$this->request->limit >= $limit) $limit = (int)$this->request->limit ?: $limit;

		$conversation = $this->findPaginatedConversation($friend_id, $limit);
		$conversation_array = $conversation->toArray();

		$was_any_message_marked_read = $this->markFetchedMessagesAsRead($conversation_array);

		if ($was_any_message_marked_read && $this->isAllMessagesRead($friend_id)) {
			$this->notifyFriend($friend_id);
		}
		
		return $this->respondWithPagination($conversation, $this->messageTransformer);
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

		dispatch(new SendMessagesReadNotification($data, $friend_id));
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
        if ($this->isBothFileAndPost()) {
    		return $this->setStatusCode(IlluminateResponse::HTTP_UNPROCESSABLE_ENTITY)->respondWithError(['message'=>'A message can not be of both file and post type at the same time, Check your is_file and is_post key.']);
    	}
    	if ($this->isReceiverSameAsSender()) {
    		return $this->setStatusCode(IlluminateResponse::HTTP_UNPROCESSABLE_ENTITY)->respondWithError(['message'=>'One can not send a message to himself/herself']);
    	}
    	
    	try {
    		$message = $this->createMessage($request);
    	} catch (QueryException $e) {
    		return $this->setStatusCode(IlluminateResponse::HTTP_NOT_FOUND)->respondWithError('Receiver id not found!');
    	}
    	
    	dispatch(new SendNewMessageNotification($message));
    	$this->trackAction(Auth::user(), "New Message");

    	$this->incrementMessageCount($request->user()->id);
    	$this->updateTotalMessageCountInParticipantsTable($request->user()->id, $request->receiver_id, $message->id);
    	$this->updateMessageCountInFollowersTable($request->receiver_id);

        return $this->respond([
        	'message' => 'Message successfully sent.',
        	'message_id' => $message->id
    	]);
    }

    /**
     * checks if sending message has both is_file and is_post key set to value 1
     * @return boolean [description]
     */
    protected function isBothFileAndPost()
    {
    	if ($this->request->is_file == 1 && $this->request->is_post == 1) {
    		return true;
    	}
    }

    /**
     * checks if the sender is trying send message to himself/herself
     * @return boolean [description]
     */
    protected function isReceiverSameAsSender()
    {
    	if ($this->request->receiver_id == $this->request->user()->id) {
    		return true;
    	}
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
    		// $message->url = $request->file('url')->store(
	     //        'img/messages', 's3'
	     //    );
	     	$path = $this->uploadPostImageTos3('url', 'img/messages');
	     	$message->url = $path;
    	}
    	if ($request->is_post) {
    		$message->is_post = $request->is_post;
    		$message->url = $request->url;
    	}

		$message->save();
		return $message;
    }

    /**
     * delete all messages sent/received between a friend
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function delete(Request $request)
    {
    	$friend = User::find($request->friend_id);
    	if(!$friend)
    		return $this->responseNotFound('The User does not exist.');
    	$participant_ids = [ $request->user()->id, $request->friend_id ];

    	$this->deleteEntryInMessageParticipantTable($participant_ids);
    	$this->deleteAllMessages($request->user()->id, $request->friend_id);

    	return $this->respond([ 'message' => 'All messages has been deleted.' ]);
    }

    /**
     * deletes the row from message_participants
     * @param  [type] $participant_ids [description]
     * @return [type]                  [description]
     */
    protected function deleteEntryInMessageParticipantTable($participant_ids)
    {
    	return MessageParticipant::whereIN('participant_one', $participant_ids)->whereIN('participant_two', $participant_ids)->delete();
    }

    protected function deleteAllMessages($sender_id, $receiver_id)
    {
    	$owner_message_count = $this->message->where('sender_id', $sender_id)->where('receiver_id', $receiver_id)->delete();
    	$receiver_message_count = $this->message->where('sender_id', $receiver_id)->where('receiver_id', $sender_id)->delete();

    	$this->decrementMessageCount($sender_id, $owner_message_count);
    	$this->decrementMessageCount($receiver_id, $receiver_message_count);
    }
}

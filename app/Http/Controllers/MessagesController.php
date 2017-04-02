<?php

namespace App\Http\Controllers;

use App\Message;
use App\Events\MessageSent;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Http\Response as IlluminateResponse;

class MessagesController extends ApiController
{
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

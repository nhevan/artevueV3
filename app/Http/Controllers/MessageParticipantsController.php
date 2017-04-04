<?php

namespace App\Http\Controllers;

use App\MessageParticipant;
use Illuminate\Http\Request;
use Acme\Transformers\MessageParticipantTransformer;

class MessageParticipantsController extends ApiController
{
	protected $messageParticipant;
    
    /**
     * Acme/Transformers/messageParticipantTransformer
     * @var messageParticipantTransformer
     */
    protected $messageParticipantTransformer;

    public function __construct(MessageParticipant $messageParticipant, MessageParticipantTransformer $messageParticipantTransformer)
    {
        $this->messageParticipant = $messageParticipant;
        $this->messageParticipantTransformer = $messageParticipantTransformer;
    }

    /**
     * returns the friends list and their last message
     * @param  Request $request [description]
     * @return [type]           [description]
     */
	public function index(Request $request)
	{
		$message_history = $this->messageParticipant->where('participant_one', $request->user()->id)->orWhere('participant_two', $request->user()->id)->with('lastMessage', 'participantOneData', 'participantTwoData')->paginate(10);

		return $this->respondWithPagination($message_history, $this->messageParticipantTransformer);
	}
}

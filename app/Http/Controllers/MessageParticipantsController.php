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

	public function index(Request $request)
	{
		$message_history = $this->messageParticipant->where('participant_one', $request->user()->id)->orWhere('participant_two', $request->user()->id)->with('lastMessage', 'participantOneData', 'participantTwoData')->paginate(10);

		// return $this->respond($message_history);
		return $this->respondWithPagination($message_history, $this->messageParticipantTransformer);
	}
}

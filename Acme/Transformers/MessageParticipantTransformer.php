<?php

namespace Acme\Transformers;

use Illuminate\Support\Facades\Auth;

/**
*
*/
class MessageParticipantTransformer extends Transformer
{
    public function transform($message_participant)
    {	
        // var_dump(Auth::user()->id);
        // exit();
        if (Auth::user()->id == $message_participant['participant_one']) {
            $participant = $message_participant['participant_two_data'];
        }else{
            $participant = $message_participant['participant_one_data'];
        }

        return [
                'id' => $message_participant['id'],
                'user_id' => $participant['id'],
                'username' => $participant['username'],
                'name' => $participant['name'],
                'profile_picture' => $participant['profile_picture'],
                'last_message_id' => $message_participant['last_message_id'],
                'total_messages' => $message_participant['total_messages'],
                'last_message_at' => $message_participant['updated_at'],
                'last_message' => $message_participant['last_message'],
            ];
    }
}
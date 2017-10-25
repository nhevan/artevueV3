<?php

namespace App;

use App\MessageParticipant;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $table = 'messages';

    public function receiver()
    {
    	return $this->belongsTo('App\User', 'receiver_id');
    }

    public function sender()
    {
    	return $this->belongsTo('App\User', 'sender_id');
    }

    public function getUnreadConversationLastMessageCount()
    {
    	$available_conversations = MessageParticipant::where('participant_one',$this->receiver->id)->orWhere('participant_two',$this->receiver->id)->get();
    	$unread_count = 0;

    	foreach ($available_conversations as $conversation) {
    		if ($conversation->lastMessage->sender_id != $this->receiver->id) {
				if (!$conversation->lastMessage->is_read) {
					$unread_count++;
				}
    		}
    	}

    	return $unread_count;
    }
}

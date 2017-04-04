<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MessageParticipant extends Model
{
	protected $table = 'message_participants';

	protected $fillable = [
		'participant_one', 'participant_two', 'last_message_id'
	];

	public function lastMessage()
	{
		return $this->belongsTo('App\Message', 'last_message_id');
	}

	public function participantOneData()
	{
		return $this->belongsTo('App\User', 'participant_one');
	}

	public function participantTwoData()
	{
		return $this->belongsTo('App\User', 'participant_two');
	}
}

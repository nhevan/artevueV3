<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class MessageTest extends TestCase
{
	use DatabaseTransactions;

	/**
     * @test
     * a message object can fetch the total number of conversations with the last message unread for the receiver
     */
    public function a_message_object_can_fetch_the_total_number_of_conversations_with_the_last_message_unread_for_the_receiver()
    {
    	//arrange
    	$receiver = factory('App\UserMetadata')->create()->user;
    	$sender_one = factory('App\UserMetadata')->create()->user;
    	$sender_two = factory('App\UserMetadata')->create()->user;
    	$sender_three = factory('App\UserMetadata')->create()->user;
        
        //act
    	$unread_message_one = $this->createMessage($sender_one, $receiver, 0);
    	$unread_message_two = $this->createMessage($sender_two, $receiver, 0);
    	$this->createMessage($sender_three, $receiver, 1);
    
        //assert
        $this->assertEquals(2, $unread_message_one->getUnreadConversationLastMessageCount());
    }

    /**
     * @test
     * only messages that the receiver did not read should counted and returned when getUnreadConversationLastMessageCount method is called
     */
    public function only_messages_that_the_receiver_did_not_read_should_counted_and_returned_when_getUnreadConversationLastMessageCount_method_is_called()
    {
    	//arrange
        $ben = factory('App\UserMetadata')->create()->user;
    	$me = factory('App\UserMetadata')->create()->user;
    	$another_user = factory('App\UserMetadata')->create()->user;
    
        //act
    	$unread_message_one = $this->createMessage($ben, $me, 0);
    	$unread_message_two = $this->createMessage($another_user, $ben, 0);
    
        //assert
        $this->assertEquals(1, $unread_message_one->getUnreadConversationLastMessageCount());
        $this->assertEquals(1, $unread_message_two->getUnreadConversationLastMessageCount());
    }

    /**
     * creates a dummy message from a given sender receiver and is_read tag, also creates a corresponding message participant row 
     * @param  [type]  $sender   [description]
     * @param  [type]  $receiver [description]
     * @param  integer $is_read  [description]
     * @return [type]            [description]
     */
    public function createMessage($sender, $receiver, $is_read = 0)
    {
    	$message = factory('App\Message')->create([
        	'sender_id' => $sender->id,
        	'receiver_id' => $receiver->id,
        	'is_read' => $is_read
    	]);
        $conversation = factory('App\MessageParticipant')->create([
        	'participant_one' => $sender,
        	'participant_two' => $receiver,
        	'last_message_id' => $message->id
    	]);

        return $message;
    }
}

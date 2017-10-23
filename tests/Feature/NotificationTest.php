<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class NotificationTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @test
     * a onesignal notification is sent when a new message is sent
     */
    public function a_onesignal_notification_is_sent_when_a_new_message_is_sent()
    {
    	//arrange
    	$sender = factory('App\User')->create();
        $this->signIn($sender);
    	$receiver = factory('App\User')->create(['id'=>176]);
    	factory('App\UserMetadata')->create(['user_id' => $sender->id]);

        //act
    	$response = $this->json('POST','api/message',[
    		'receiver_id' => $receiver->id,
    		'message' => 'Testing OneSignal - unit test'
		]);
    
        //assert
        $response->assertJsonFragment(['Message successfully sent.']);
    }
}

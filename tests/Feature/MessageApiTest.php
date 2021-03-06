<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class MessageApiTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();

    	Queue::fake();
    }

    /**
     * @test
     * a user can send a text message to another user
     */
    public function a_user_can_send_a_text_message_to_another_user()
    {
    	//arrange
        $sender = factory('App\User')->create();
    	$receiver = factory('App\User')->create();
    	factory('App\UserMetadata')->create(['user_id' => $sender->id]);
        $this->signIn($sender);

        //act
    	$response = $this->json('POST','api/message',[
    		'receiver_id' => $receiver->id,
    		'message' => 'Test Message'
		]);
    
        //assert
        $this->assertDatabaseHas('messages', [
        	'receiver_id' => $receiver->id,
        	'sender_id' => $sender->id,
        	'message' => 'Test Message',
        	'is_file' => 0,
        	'is_post' => 0,
        	'url' => null
    	]);
    }

    /**
     * @test
     * a user can send a post to another user as a message
     */
    public function a_user_can_send_a_post_to_another_user_as_a_message()
    {
    	//arrange
        $sender = factory('App\User')->create();
    	$receiver = factory('App\User')->create();
    	$post = factory('App\Post')->create();
    	factory('App\UserMetadata')->create(['user_id' => $sender->id]);
        $this->signIn($sender);
    
        //act
    	$response = $this->json('POST','api/message',[
    		'receiver_id' => $receiver->id,
    		'message' => 'Post Message',
    		'is_post' => $post->id,
    		'url' => $post->image
		]);
    
        //assert
        $this->assertDatabaseHas('messages', [
        	'receiver_id' => $receiver->id,
        	'sender_id' => $sender->id,
        	'message' => 'Post Message',
        	'is_file' => 0,
        	'is_post' => $post->id,
        	'url' => $post->image
    	]);
    }

    /**
     * @test
     * a user can send a file via message in older versions
     */
    public function a_user_can_send_a_file_via_message_in_older_versions()
    {
    	//arrange
        $sender = factory('App\User')->create();
    	$receiver = factory('App\User')->create();
    	factory('App\UserMetadata')->create(['user_id' => $sender->id]);
        $this->signIn($sender);
    
        //act
    	$response = $this->json('POST','api/message',[
    		'receiver_id' => $receiver->id,
    		'message' => 'File Message',
    		'is_file' => 1,
    		'url' => UploadedFile::fake()->image('avatar.jpg')
		]);

        //assert
        $this->assertDatabaseHas('messages', [
        	'receiver_id' => $receiver->id,
        	'sender_id' => $sender->id,
        	'message' => 'File Message',
        	'is_file' => 1,
        	'is_post' => 0
    	]);
    	$this->assertDatabaseMissing('messages', [
        	'receiver_id' => $receiver->id,
        	'sender_id' => $sender->id,
        	'url' => null
    	]);
    }

    /**
     * @test
     * a user can send a file via message in newer version of the app using base64
     */
    public function a_user_can_send_a_file_via_message_in_newer_version_of_the_app_using_base64()
    {
    	//arrange
        $sender = factory('App\User')->create();
    	$receiver = factory('App\User')->create();
    	factory('App\UserMetadata')->create(['user_id' => $sender->id]);
        $this->signIn($sender);
    
        //act
    	$response = $this->json('POST','api/message',[
    		'receiver_id' => $receiver->id,
    		'message' => 'File Message',
    		'is_file' => 1,
    		'url' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAABREAAAJPCAYAAADrIZMWAAABfGlDQ1BJQ0MgUHJvZmlsZQAAKJFjYGAqSSwoyGFhYGDIzSspCnJ3UoiIjFJgv8PAzcDDIMRgxSCemFxc4BgQ4MOAE3y7xsAIoi/rgsxK8/x506a1fP4WNq+ZclYlOrj1gQF3SmpxMgMDIweQnZxSnJwLZOcA2TrJBUUlQPYMIFu3vKQAxD4BZIsUAR0IZN8BsdMh7A8gdhKYzcQCVhMS5AxkSwDZAkkQtgaInQ5hW4DYyRmJKUC2B8guiBvAgNPDRcHcwFLXkYC7SQa5OaUwO0ChxZOaFxoMcgcQyzB4MLgwKDCYMxgwWDLoMjiWpFaUgBQ65xdUFmWmZ5QoOAJDNlXBOT+3oLQktUhHwTMvWU9HwcjA0ACkDhRnEKM/B4FNZxQ7jxDLX8jAYKnMwMDcgxBLmsbAsH0PA4PEKYSYyjwGBn5rBoZt5woSixLhDmf8xkKIX5xmbARh8zgxMLDe+///sxoDA/skBoa/E////73'
		],
		[
			'X-ARTEVUE-App-Version' => '2.1'
		]);
    
        //assert
        $this->assertDatabaseHas('messages', [
        	'receiver_id' => $receiver->id,
        	'sender_id' => $sender->id,
        	'message' => 'File Message',
        	'is_file' => 1,
        	'is_post' => 0
    	]);
    	$this->assertDatabaseMissing('messages', [
        	'receiver_id' => $receiver->id,
        	'sender_id' => $sender->id,
        	'url' => null
    	]);
    }

    /**
     * @test
     * when a message is sent to a user for the first time a new message participant row is created on the desired table
     */
    public function when_a_message_is_sent_to_a_user_for_the_first_time_a_new_message_participant_row_is_created_on_the_desired_table()
    {
        //arrange
        $sender = factory('App\User')->create();
        $receiver = factory('App\User')->create();
        factory('App\UserMetadata')->create(['user_id' => $sender->id]);
        factory('App\UserMetadata')->create(['user_id' => $receiver->id]);
        $this->signIn($sender);
    
        //act
        $response = $this->json('POST','api/message',[
            'receiver_id' => $receiver->id,
            'message' => 'Testing new entry message participant table.',
        ]);
    
        //assert
        $this->assertDatabaseHas('message_participants', [
            'participant_one' => $sender->id,
            'participant_two' => $receiver->id,
            'total_messages' => 1
        ]);
    }

    /**
     * @test
     * total messages count increases in message participant table when a new message is sent for both way messaging
     */
    public function total_messages_count_increases_in_message_participant_table_when_a_new_message_is_sent()
    {
        //arrange
        $userA = factory('App\User')->create();
        $userB = factory('App\User')->create();
        factory('App\UserMetadata')->create(['user_id' => $userA->id]);
        factory('App\UserMetadata')->create(['user_id' => $userB->id]);
        $this->signIn($userA);
    
        //act
        $first_message_to_user_b = $this->json('POST','api/message',[
            'receiver_id' => $userB->id,
            'message' => 'Testing new entry message participant table.',
        ]);

        $second_message_to_user_b = $this->json('POST','api/message',[
            'receiver_id' => $userB->id,
            'message' => 'Testing new entry message participant table.',
        ]);

        $this->signIn($userB);
        $first_message_to_user_a = $this->json('POST','api/message',[
            'receiver_id' => $userA->id,
            'message' => 'Testing new entry message participant table.',
        ]);
    
        //assert
        $this->assertDatabaseHas('message_participants', [
            'participant_one' => $userA->id,
            'participant_two' => $userB->id,
            'total_messages' => 3
        ]);
    }

    /**
     * @test
     * when a message is sent to a user the message participant table is updated to point to the last message
     */
    public function when_a_message_is_sent_to_a_user_the_message_participant_table_is_updated_to_point_to_the_last_message()
    {
        //arrange
        $sender = factory('App\User')->create();
        $receiver = factory('App\User')->create();
        factory('App\UserMetadata')->create(['user_id' => $sender->id]);
        factory('App\UserMetadata')->create(['user_id' => $receiver->id]);
        $this->signIn($sender);
        $message1 = $this->json('POST','api/message',[
            'receiver_id' => $receiver->id,
            'message' => 'Testing new entry message participant table.',
        ]);

        //act
        $message2 = $this->json('POST','api/message',[
            'receiver_id' => $receiver->id,
            'message' => 'Second Message',
        ]);
        $this->signIn($receiver);
        $message3 = $this->json('POST','api/message',[
            'receiver_id' => $sender->id,
            'message' => 'Third Message as a reply',
        ]);

        //assert
        $this->assertDatabaseHas('message_participants', [
            'participant_one' => $sender->id,
            'participant_two' => $receiver->id,
            'total_messages' => 3,
            'last_message_id' => $message3->json()['message_id']
        ]);
    }

    /**
     * @test
     * a user can delete a entire conversation
     */
    public function a_user_can_delete_a_entire_conversation()
    {
        //arrange
        $sender = factory('App\User')->create();
        $receiver = factory('App\User')->create();
        factory('App\UserMetadata')->create(['user_id' => $sender->id]);
        factory('App\UserMetadata')->create(['user_id' => $receiver->id]);
        $this->signIn($sender);
    
        $this->json('POST','api/message',[
            'receiver_id' => $receiver->id,
            'message' => 'Testing new entry message participant table.',
        ]);

        //act
        $response = $this->json('DELETE', "api/conversation/{$receiver->id}");
    
        //assert
        $this->assertDatabaseMissing('message_participants', [
            'participant_one' => $sender->id,
            'participant_two' => $receiver->id,
            'total_messages' => 1
        ]);
        $this->assertDatabaseMissing('messages', [
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id
        ]);
    }
}

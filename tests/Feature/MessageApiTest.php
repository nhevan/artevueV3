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
}

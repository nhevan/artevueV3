<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Jobs\SendNewLikeNotification;
use Illuminate\Support\Facades\Queue;
use App\Jobs\SendNewFollowerNotification;
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
    	$receiver = factory('App\User')->create(['id'=>1]);
    	factory('App\UserMetadata')->create(['user_id' => $sender->id]);

        //act
    	$response = $this->json('POST','api/message',[
    		'receiver_id' => $receiver->id,
    		'message' => 'Testing OneSignal - unit test'
		]);
    
        //assert
        $response->assertJsonFragment(['Message successfully sent.']);
    }

    /**
     * @test
     * a notification is sent to a user who is just being followed by another user
     */
    public function a_notification_is_sent_to_a_user_who_is_just_being_followed_by_another_user()
    {
        //arrange
        $follower = factory('App\User')->create();
        $this->signIn($follower);
        $followed_user = factory('App\User')->create(['id'=>1]);
        factory('App\UserMetadata')->create(['user_id' => $follower->id]);
        factory('App\UserMetadata')->create(['user_id' => $followed_user->id]);
    
        //act
        Queue::fake();
        $response = $this->post('api/follow/'.$followed_user->id);

        //assert
        Queue::assertPushed(SendNewFollowerNotification::class, function($new_follower_notification) use ($follower){
            $expected_notification_text = "{$follower->name}({$follower->username}) started following you.";

            return $new_follower_notification->notification_text === $expected_notification_text;
        });        
    }

    /**
     * @test
     * a notification is sent to the post owner when someone likes a post
     */
    public function a_notification_is_sent_to_the_post_owner_when_someone_likes_a_post()
    {
        //arrange
        $liker = factory('App\User')->create();
        $this->signIn($liker);
        $owner = factory('App\User')->create(['id'=>1]);
        $post = factory('App\Post')->create(['owner_id' => $owner->id]);
        factory('App\UserMetadata')->create(['user_id' => $liker->id]);
        factory('App\UserMetadata')->create(['user_id' => $owner->id]);

        //act
        Queue::fake();
        $response = $this->post('api/like/'.$post->id);

        //assert
        Queue::assertPushed(SendNewLikeNotification::class, function($new_like) use ($liker){
            $expected_notification_text = "{$liker->name}({$liker->username}) liked your post.";

            return $new_like->notification_text === $expected_notification_text;
        }); 
    }
}

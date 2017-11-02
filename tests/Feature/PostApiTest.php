<?php

namespace Tests\Feature;

use App\Post;
use App\User;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Http\Request;
use App\Events\NewBuyPostRequest;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use App\Jobs\SendNewMessageNotification;
use App\Listeners\SendBuyPostRequestNotifications;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PostApiTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @test
     * a user can fetch all posts with predefined hashtag - in this case arteprize2017
     */
    public function a_user_can_fetch_arteprize_related_posts()
    {
    	$post = factory('App\Post')->create(['description' => '#artePrize2017']);

    	$response = $this->getJson('/api/posts/arteprize')->json();

    	$this->assertArrayHasKey('data', $response);
    	$this->assertArrayHasKey('pagination', $response);
    	$this->assertEquals([$post->id], array_column($response['data'], 'id'));
    }

    /**
     * @test
     * arteprize posts does not include undiscoverable posts
     */
    public function arteprize_posts_does_not_include_undiscoverable_posts()
    {
        //arrange
        $post = factory('App\Post')->create(['description' => '#artePrize2017']);
        $undiscoverable_post = factory('App\Post')->create(['description' => '#artePrize2017', 'is_undiscoverable' => 1]);
    
        //act
        $response = $this->getJson('/api/posts/arteprize')->json();
    
        //assert
        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('pagination', $response);
        $this->assertEquals([$post->id], array_column($response['data'], 'id'));
    }

    /**
     * @test
     * arteprize posts appear in chronological order
     */
    public function arteprize_posts_appear_in_chronological_order()
    {
    	//arrange
        $post_old = factory('App\Post')->create(['description' => '#artePrize2017', 'created_at' => Carbon::now()->subHours(2)]);
        $post_recent = factory('App\Post')->create(['description' => '#artePrize2017']);
    
        //act
    	$response = $this->getJson('/api/posts/arteprize')->json();
    
        //assert
        $this->assertEquals([ $post_recent->id, $post_old->id ], array_column($response['data'], 'id'));
    }

    /**
     * @test
     * a user can fetch all artevue selected posts
     */
    public function a_user_can_fetch_artevue_selected_posts()
    {
    	$selected_post = factory('App\Post')->create(['is_selected_by_artevue' => 1]);
        $general_post = factory('App\Post')->create();
    	$response = $this->getJson('/api/posts/selected')->json();

    	$this->assertArrayHasKey('data', $response);
    	$this->assertArrayHasKey('pagination', $response);
    	$this->assertEquals([$selected_post->id], array_column($response['data'], 'id'));
    }       

    /**
     * @test
     * selected posts appear in chronological order
     */
    public function selected_posts_appear_in_chronological_order()
    {
    	//arrange
        $post_old = factory('App\Post')->create(['is_selected_by_artevue' => 1, 'created_at' => Carbon::now()->subHours(2)]);
        $post_recent = factory('App\Post')->create(['is_selected_by_artevue' => 1]);
    
        //act
        $response = $this->getJson('/api/posts/selected')->json();
    
        //assert
        $this->assertEquals([ $post_recent->id, $post_old->id ], array_column($response['data'], 'id'));
    }

    /**
     * @test
     * a user can fetch all posts selected for sale
     */
    public function a_user_can_fetch_all_posts_selected_for_sale()
    {
    	$post = factory('App\Post')->create(['is_selected_for_sale' => 1]);
        factory('App\Post')->create();
    	$response = $this->getJson('/api/posts/sale')->json();

    	$this->assertArrayHasKey('data', $response);
    	$this->assertArrayHasKey('pagination', $response);
    	$this->assertEquals([$post->id], array_column($response['data'], 'id'));
    }

    /**
     * @test
     * on sale posts appear in chronological order
     */
    public function on_sale_posts_appear_in_chronological_order()
    {
    	//arrange
        $post_old = factory('App\Post')->create([ 'is_selected_for_sale' => 1, 'created_at' => Carbon::now()->subHours(2)]);
        $post_recent = factory('App\Post')->create([ 'is_selected_for_sale' => 1 ]);
    
        //act
        $response = $this->getJson('/api/posts/sale')->json();
    
        //assert
        $this->assertEquals([ $post_recent->id, $post_old->id ], array_column($response['data'], 'id'));
    }

    /**
     * @test
     * when a post is liked, its like_count increases by 1
     */
    public function when_a_post_is_liked_its_like_count_increases_by_1()
    {
    	//arrange
    	$this->signIn();
    	$postOwner = factory('App\UserMetadata')->create();
        $post = factory('App\Post')->create(['owner_id' => $postOwner->user_id]);
    
        //act
    	$response = $this->post("/api/like/{$post->id}")->json();
    
        //assert
        $this->assertDatabaseHas('posts', [
        		'id' => $post->id,
        		'like_count' => 1
        	]);
    }

    /**
     * @test
     * when a post is unliked, its like_count deccreases by 1
     */
    public function when_a_post_is_unliked_its_like_count_deccreases_by_1()
    {
    	//arrange
    	$this->signIn();
    	$postOwner = factory('App\UserMetadata')->create();
        $post = factory('App\Post')->create(['owner_id' => $postOwner->user_id]);
    
        //act
        $response = $this->post("/api/like/{$post->id}")->json();    // first like the most
    	$response = $this->delete("/api/like/{$post->id}")->json();  // then unlike the post

        //assert
        $this->assertDatabaseHas('posts', [
        		'id' => $post->id,
        		'like_count' => 0
        	]);
    }

    /**
     * @test
     * post art type can be set when a post is being created by the user
     */
    public function post_art_type_can_be_set_when_a_post_is_being_created_by_the_user()
    {
        //arrange
        $this->signIn();
        Storage::fake('s3');

        //act
        $response = $this->post("/api/post",[
                'post_image' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAABREAAAJPCAYAAADrIZMWAAABfGlDQ1BJQ0MgUHJvZmlsZQAAKJFjYGAqSSwoyGFhYGDIzSspCnJ3UoiIjFJgv8PAzcDDIMRgxSCemFxc4BgQ4MOAE3y7xsAIoi/rgsxK8/x506a1fP4WNq+ZclYlOrj1gQF3SmpxMgMDIweQnZxSnJwLZOcA2TrJBUUlQPYMIFu3vKQAxD4BZIsUAR0IZN8BsdMh7A8gdhKYzcQCVhMS5AxkSwDZAkkQtgaInQ5hW4DYyRmJKUC2B8guiBvAgNPDRcHcwFLXkYC7SQa5OaUwO0ChxZOaFxoMcgcQyzB4MLgwKDCYMxgwWDLoMjiWpFaUgBQ65xdUFmWmZ5QoOAJDNlXBOT+3oLQktUhHwTMvWU9HwcjA0ACkDhRnEKM/B4FNZxQ7jxDLX8jAYKnMwMDcgxBLmsbAsH0PA4PEKYSYyjwGBn5rBoZt5woSixLhDmf8xkKIX5xmbARh8zgxMLDe+///sxoDA/skBoa/E////73',
                'description' => 'test description',
                'post_art_type_id' => 1
            ],[
                'X-ARTEVUE-App-Version' => '2'
            ]); 

        //assert
        $this->assertDatabaseHas('posts', [
                'description' => 'test description',
                'post_art_type_id' => 1
            ]);
    }

    /**
     * @test
     * a request can be sent to buy a post
     */
    public function a_request_can_be_sent_to_buy_a_post()
    {
        //arrange
        $this->signIn();
        $postOwner = factory('App\UserMetadata')->create();
        $post = factory('App\Post')->create(['owner_id' => $postOwner->user_id, 'has_buy_btn' => 1, 'price' => '100']);

        //act
        $response = $this->get("/api/post/detail/{$post->id}/buy");

        //assert
        $response->assertStatus(200);
        $response->assertJsonFragment([
            "message" => "Post buy notification successgully sent."
        ]);
        //assert a push notification is sent to the post via both onesignal and pusher
        //a email is sent to the post owner
    }

    /**
     * @test
     * a NewBuyPostRequest event is diapatched when someone requests to buy a post
     */
    public function a_NewBuyPostRequest_event_is_diapatched_containing_the_post_when_someone_requests_to_buy_that_post()
    {
        //arrange
        Event::fake();
        $this->signIn();
        $postOwner = factory('App\UserMetadata')->create();
        $post = factory('App\Post')->create(['owner_id' => $postOwner->user_id, 'has_buy_btn' => 1, 'price' => '100']);

        //act
        $response = $this->get("/api/post/detail/{$post->id}/buy");

        //assert
        Event::assertDispatched(NewBuyPostRequest::class, function ($e) use ($post) {
            return $e->post->id === $post->id && $e->interested_user->id === $this->user->id;
        });
    }

    /**
     * @test
     * when a user requests to buy a post a message is sent to the post owner from the requested user
     */
    public function when_a_user_requests_to_buy_a_post_a_message_is_sent_to_the_post_owner_from_the_requested_user()
    {
        //arrange
        $this->signIn();

        $postOwner = factory('App\User')->create(['id'=>1]);
        factory('App\UserMetadata')->create(['user_id' => 1]);
        $post = factory('App\Post')->create(['owner_id' => $postOwner->id, 'has_buy_btn' => 1, 'price' => '100']);

        //act
        $response = $this->get("/api/post/detail/{$post->id}/buy");
    
        //assert
        $this->assertDatabaseHas('messages', [
            'receiver_id' => $postOwner->id,
            'sender_id' => $this->user->id,
            'is_post' => $post->id,
            'url' => $post->image
        ]);
        $this->assertDatabaseHas('message_participants', [
            'participant_one' => $this->user->id,
            'participant_two' => $postOwner->id,
            'total_messages' => 1
        ]);
    }
}

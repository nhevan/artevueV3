<?php

namespace Tests\Feature;

use App\Post;
use App\User;
use App\Artist;
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
     * a logged in user can create a post
     */
    public function a_logged_in_user_can_create_a_post()
    {
        //arrange
        Storage::fake('s3');
        $this->signIn();
        $post = factory('App\Post')->make( ['post_image' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAABREAAAJPCAYAAADrIZMWAAABfGlDQ1BJQ0MgUHJvZmlsZQAAKJFjYGAqSSwoyGFhYGDIzSspCnJ3UoiIjFJgv8PAzcDDIMRgxSCemFxc4BgQ4MOAE3y7xsAIoi/rgsxK8/x506a1fP4WNq+ZclYlOrj1gQF3SmpxMgMDIweQnZxSnJwLZOcA2TrJBUUlQPYMIFu3vKQAxD4BZIsUAR0IZN8BsdMh7A8gdhKYzcQCVhMS5AxkSwDZAkkQtgaInQ5hW4DYyRmJKUC2B8guiBvAgNPDRcHcwFLXkYC7SQa5OaUwO0ChxZOaFxoMcgcQyzB4MLgwKDCYMxgwWDLoMjiWpFaUgBQ65xdUFmWmZ5QoOAJDNlXBOT+3oLQktUhHwTMvWU9HwcjA0ACkDhRnEKM/B4FNZxQ7jxDLX8jAYKnMwMDcgxBLmsbAsH0PA4PEKYSYyjwGBn5rBoZt5woSixLhDmf8xkKIX5xmbARh8zgxMLDe+///sxoDA/skBoa/E////73'] )->toArray();

        //act
        $response = $this->post('api/post', $post , [ 'X-ARTEVUE-App-Version' => '2.0' ]);

        //assert
        $response->assertSuccessful();
        $this->assertDatabaseHas('posts', ['owner_id' => $this->user->id]);
    }

    /**
     * @test
     * a user can speicify a gallery the post belongs to
     */
    public function a_user_can_speicify_a_gallery_the_post_belongs_to()
    {
        //arrange
        Storage::fake('s3');
        $this->signIn();
        $gallery = factory('App\Gallery')->create(['user_id' => $this->user->id]);
        $post = factory('App\Post')->make([
            'post_image' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAABREAAAJPCAYAAADrIZMWAAABfGlDQ1BJQ0MgUHJvZmlsZQAAKJFjYGAqSSwoyGFhYGDIzSspCnJ3UoiIjFJgv8PAzcDDIMRgxSCemFxc4BgQ4MOAE3y7xsAIoi/rgsxK8/x506a1fP4WNq+ZclYlOrj1gQF3SmpxMgMDIweQnZxSnJwLZOcA2TrJBUUlQPYMIFu3vKQAxD4BZIsUAR0IZN8BsdMh7A8gdhKYzcQCVhMS5AxkSwDZAkkQtgaInQ5hW4DYyRmJKUC2B8guiBvAgNPDRcHcwFLXkYC7SQa5OaUwO0ChxZOaFxoMcgcQyzB4MLgwKDCYMxgwWDLoMjiWpFaUgBQ65xdUFmWmZ5QoOAJDNlXBOT+3oLQktUhHwTMvWU9HwcjA0ACkDhRnEKM/B4FNZxQ7jxDLX8jAYKnMwMDcgxBLmsbAsH0PA4PEKYSYyjwGBn5rBoZt5woSixLhDmf8xkKIX5xmbARh8zgxMLDe+///sxoDA/skBoa/E////73',
            'is_gallery' => $gallery->id
        ])->toArray();
    
        //act
        $response = $this->post('api/post', $post , [ 'X-ARTEVUE-App-Version' => '2.0' ]);

        //assert
        $this->assertDatabaseHas('pins', ['user_id' => $this->user->id, 'gallery_id' => $gallery->id]);
    }

    /**
     * @test
     * a user can specify multiple galleries separated by comma while creating a post
     */
    public function a_user_can_specify_multiple_galleries_separated_by_comma_while_creating_a_post()
    {
        //arrange
        Storage::fake('s3');
        $this->signIn();
        $gallery1 = factory('App\Gallery')->create(['user_id' => $this->user->id]);
        $gallery2 = factory('App\Gallery')->create(['user_id' => $this->user->id]);
        $post = factory('App\Post')->make([
            'post_image' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAABREAAAJPCAYAAADrIZMWAAABfGlDQ1BJQ0MgUHJvZmlsZQAAKJFjYGAqSSwoyGFhYGDIzSspCnJ3UoiIjFJgv8PAzcDDIMRgxSCemFxc4BgQ4MOAE3y7xsAIoi/rgsxK8/x506a1fP4WNq+ZclYlOrj1gQF3SmpxMgMDIweQnZxSnJwLZOcA2TrJBUUlQPYMIFu3vKQAxD4BZIsUAR0IZN8BsdMh7A8gdhKYzcQCVhMS5AxkSwDZAkkQtgaInQ5hW4DYyRmJKUC2B8guiBvAgNPDRcHcwFLXkYC7SQa5OaUwO0ChxZOaFxoMcgcQyzB4MLgwKDCYMxgwWDLoMjiWpFaUgBQ65xdUFmWmZ5QoOAJDNlXBOT+3oLQktUhHwTMvWU9HwcjA0ACkDhRnEKM/B4FNZxQ7jxDLX8jAYKnMwMDcgxBLmsbAsH0PA4PEKYSYyjwGBn5rBoZt5woSixLhDmf8xkKIX5xmbARh8zgxMLDe+///sxoDA/skBoa/E////73',
            'is_gallery' => "{$gallery1->id}, {$gallery2->id}"
        ])->toArray();
    
        //act
        $response = $this->post('api/post', $post , [ 'X-ARTEVUE-App-Version' => '2.0' ]);

        //assert
        $this->assertDatabaseHas('pins', ['user_id' => $this->user->id, 'gallery_id' => $gallery1->id]);
        $this->assertDatabaseHas('pins', ['user_id' => $this->user->id, 'gallery_id' => $gallery2->id]);
    }

    /**
     * @test
     * a user can specify multiple galleries in array format
     */
    public function a_user_can_specify_multiple_galleries_in_array_format()
    {
        //arrange
        Storage::fake('s3');
        $this->signIn();
        $gallery1 = factory('App\Gallery')->create(['user_id' => $this->user->id]);
        $gallery2 = factory('App\Gallery')->create(['user_id' => $this->user->id]);
        $post = factory('App\Post')->make([
            'post_image' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAABREAAAJPCAYAAADrIZMWAAABfGlDQ1BJQ0MgUHJvZmlsZQAAKJFjYGAqSSwoyGFhYGDIzSspCnJ3UoiIjFJgv8PAzcDDIMRgxSCemFxc4BgQ4MOAE3y7xsAIoi/rgsxK8/x506a1fP4WNq+ZclYlOrj1gQF3SmpxMgMDIweQnZxSnJwLZOcA2TrJBUUlQPYMIFu3vKQAxD4BZIsUAR0IZN8BsdMh7A8gdhKYzcQCVhMS5AxkSwDZAkkQtgaInQ5hW4DYyRmJKUC2B8guiBvAgNPDRcHcwFLXkYC7SQa5OaUwO0ChxZOaFxoMcgcQyzB4MLgwKDCYMxgwWDLoMjiWpFaUgBQ65xdUFmWmZ5QoOAJDNlXBOT+3oLQktUhHwTMvWU9HwcjA0ACkDhRnEKM/B4FNZxQ7jxDLX8jAYKnMwMDcgxBLmsbAsH0PA4PEKYSYyjwGBn5rBoZt5woSixLhDmf8xkKIX5xmbARh8zgxMLDe+///sxoDA/skBoa/E////73',
            'is_gallery' => [ $gallery1->id, $gallery2->id ]
        ])->toArray();
    
        //act
        $response = $this->post('api/post', $post , [ 'X-ARTEVUE-App-Version' => '2.0' ]);

        //assert
        $this->assertDatabaseHas('pins', ['user_id' => $this->user->id, 'gallery_id' => $gallery1->id]);
        $this->assertDatabaseHas('pins', ['user_id' => $this->user->id, 'gallery_id' => $gallery2->id]);
    }

    /**
     * @test
     * while creating post the specified galleries must belong to the logged in user
     */
    public function while_creating_post_the_specified_galleries_must_belong_to_the_logged_in_user()
    {
        //arrange
        Storage::fake('s3');
        $this->signIn();
        $gallery = factory('App\Gallery')->create();
        $post = factory('App\Post')->make([
            'post_image' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAABREAAAJPCAYAAADrIZMWAAABfGlDQ1BJQ0MgUHJvZmlsZQAAKJFjYGAqSSwoyGFhYGDIzSspCnJ3UoiIjFJgv8PAzcDDIMRgxSCemFxc4BgQ4MOAE3y7xsAIoi/rgsxK8/x506a1fP4WNq+ZclYlOrj1gQF3SmpxMgMDIweQnZxSnJwLZOcA2TrJBUUlQPYMIFu3vKQAxD4BZIsUAR0IZN8BsdMh7A8gdhKYzcQCVhMS5AxkSwDZAkkQtgaInQ5hW4DYyRmJKUC2B8guiBvAgNPDRcHcwFLXkYC7SQa5OaUwO0ChxZOaFxoMcgcQyzB4MLgwKDCYMxgwWDLoMjiWpFaUgBQ65xdUFmWmZ5QoOAJDNlXBOT+3oLQktUhHwTMvWU9HwcjA0ACkDhRnEKM/B4FNZxQ7jxDLX8jAYKnMwMDcgxBLmsbAsH0PA4PEKYSYyjwGBn5rBoZt5woSixLhDmf8xkKIX5xmbARh8zgxMLDe+///sxoDA/skBoa/E////73',
            'is_gallery' => $gallery->id
        ])->toArray();
    
        //act
        $response = $this->post('api/post', $post , [ 'X-ARTEVUE-App-Version' => '2.0' ]);

        //assert
        $response->assertStatus(422);
        $this->assertDatabaseMissing('pins', ['user_id' => $this->user->id, 'gallery_id' => $gallery->id]);
    }

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
     * when a post is pinned, its pin_count increases by 1
     */
    public function when_a_post_is_pinned_its_pin_count_increases_by_1()
    {
        //arrange
        $this->signIn();
        $postOwner = factory('App\UserMetadata')->create();
        $post = factory('App\Post')->create(['owner_id' => $postOwner->user_id]);
        $gallery = factory('App\Gallery')->create(['user_id' => $this->user->id]);

        //act
        $response = $this->post("/api/gallery/{$gallery->id}/pin/{$post->id}")->json();
    
        //assert
        $this->assertDatabaseHas('posts', [
                'id' => $post->id,
                'pin_count' => 1
            ]);
    }

    /**
     * @test
     * when a post is unpinned its pin_count decreases by 1
     */
    public function when_a_post_is_unpinned_its_pin_count_decreases_by_1()
    {
        //arrange
        $this->signIn();
        $postOwner = factory('App\UserMetadata')->create();
        $post = factory('App\Post')->create(['owner_id' => $postOwner->user_id, 'pin_count' => 10]);
        $gallery = factory('App\Gallery')->create(['user_id' => $this->user->id]);

        //act
        $this->post("/api/gallery/{$gallery->id}/pin/{$post->id}")->json(); // pin the post
        $this->delete("/api/gallery/{$gallery->id}/pin/{$post->id}")->json(); // unpin the post
    
        //assert
        $this->assertDatabaseHas('posts', [
                'id' => $post->id,
                'pin_count' => 10
            ]);
    }

    /**
     * @test
     * when a comment is made on a post its comment count increases by 1
     */
    public function when_a_comment_is_made_on_a_post_its_comment_count_increases_by_1()
    {
        //arrange
        $this->signIn();
        $postOwner = factory('App\UserMetadata')->create();
        $post = factory('App\Post')->create(['owner_id' => $postOwner->user_id, 'comment_count' => 10]);
    
        //act
        $this->post("/api/comment/{$post->id}", [ 'comment' => 'test comment' ])->json();
    
        //assert
        $this->assertDatabaseHas('posts', [
                'id' => $post->id,
                'comment_count' => 11
            ]);
    }

    /**
     * @test
     * when a comment is deleted the posts comment count decreases by 1
     */
    public function when_a_comment_is_deleted_the_posts_comment_count_decreases_by_1()
    {
        //arrange
        $this->signIn();
        $postOwner = factory('App\UserMetadata')->create();
        $post = factory('App\Post')->create(['owner_id' => $postOwner->user_id, 'comment_count' => 10]);
        $comment = factory('App\Comment')->create([ 'user_id' => $this->user->id, 'post_id' => $post->id]);
    
        //act
        $response = $this->delete("/api/comment/{$comment->id}")->json();

        //assert
        $this->assertDatabaseHas('posts', [
                'id' => $post->id,
                'comment_count' => 9
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
        $this->seed('EmailTemplatesSeeder');
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

    /**
     * @test
     * a user can delete any of his posts
     */
    public function a_user_can_delete_any_of_his_posts()
    {
        //arrange
        $this->signIn();
        $post = factory('App\Post')->create(['owner_id' => $this->user->id]);

        //act
        $response = $this->delete("/api/post/{$post->id}");
    
        //assert
        $this->assertDatabaseMissing('posts', [
            'id' => $post->id
        ]);
    }

    /**
     * @test
     * a user can provide an artist name while creating a post
     */
    public function a_user_can_provide_an_artist_name_while_creating_a_post()
    {
        //arrange
        $this->signIn();
        Storage::fake('s3');

        //act
        $response = $this->post("/api/post",[
                'post_image' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAABREAAAJPCAYAAADrIZMWAAABfGlDQ1BJQ0MgUHJvZmlsZQAAKJFjYGAqSSwoyGFhYGDIzSspCnJ3UoiIjFJgv8PAzcDDIMRgxSCemFxc4BgQ4MOAE3y7xsAIoi/rgsxK8/x506a1fP4WNq+ZclYlOrj1gQF3SmpxMgMDIweQnZxSnJwLZOcA2TrJBUUlQPYMIFu3vKQAxD4BZIsUAR0IZN8BsdMh7A8gdhKYzcQCVhMS5AxkSwDZAkkQtgaInQ5hW4DYyRmJKUC2B8guiBvAgNPDRcHcwFLXkYC7SQa5OaUwO0ChxZOaFxoMcgcQyzB4MLgwKDCYMxgwWDLoMjiWpFaUgBQ65xdUFmWmZ5QoOAJDNlXBOT+3oLQktUhHwTMvWU9HwcjA0ACkDhRnEKM/B4FNZxQ7jxDLX8jAYKnMwMDcgxBLmsbAsH0PA4PEKYSYyjwGBn5rBoZt5woSixLhDmf8xkKIX5xmbARh8zgxMLDe+///sxoDA/skBoa/E////73',
                'description' => 'test description',
                'artist' => 'new artist'
            ],[
                'X-ARTEVUE-App-Version' => '2'
            ]); 
    
        //assert
        $this->assertDatabaseHas('artists', [
            'title' => 'new artist'
        ]);
        $artist = Artist::where('title', 'new artist')->first();
        $this->assertDatabaseHas('posts', [
            'artist_id' => $artist->id
        ]);
    }
}

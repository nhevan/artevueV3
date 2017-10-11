<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Tests\TestCase;
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
    	$post = factory('App\Post')->create(['is_selected_by_artevue' => 1]);

    	$response = $this->getJson('/api/posts/selected')->json();

    	$this->assertArrayHasKey('data', $response);
    	$this->assertArrayHasKey('pagination', $response);
    	$this->assertEquals([$post->id], array_column($response['data'], 'id'));
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
}

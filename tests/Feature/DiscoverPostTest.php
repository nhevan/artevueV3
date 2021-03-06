<?php

namespace Tests\Feature;

use App\Post;
use Carbon\Carbon;
use Tests\TestCase;
use App\Http\Controllers\DiscoverPostsController;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class DiscoverPostTest extends TestCase
{
	use DatabaseTransactions;

	protected $weights;

    public function setUp()
    {
        parent::setUp();

        $this->seed('SettingsTableSeeder');
        $this->weights = DiscoverPostsController::getPostWeightDistributionSettings();
    }
    
    /**
     * @test
     * it returns paginated json data for logges in or guest users
     */
    public function it_returns_paginated_json_data_for_logged_in_or_guest_users()
    {
    	$response = $this->getJson('/api/discover-posts')->json();

    	$this->assertArrayHasKey('data', $response);
    	$this->assertArrayHasKey('pagination', $response);
    }

    /**
     * @test
     * it does not return posts from authenticated users followers
     */
    public function it_does_not_return_posts_from_authenticated_users_followers()
    {
        $this->signIn();
    	$usersFollower = factory('App\Follower')->create(['follower_id' => $this->user->id]);
    	$post = factory('App\Post')->create(['owner_id' => $usersFollower->user_id]);

    	$response = $this->getJson('/api/discover-posts')->json();

    	$this->assertNotEquals([$post->id], array_column($response['data'], 'id'));
    }

    /**
     * @test
     * it returns posts from followers of authenticated users followers
     */
    public function it_returns_posts_from_followers_of_authenticated_users_followers()
    {
        $this->signIn();
        $usersFollower = factory('App\Follower')->create(['follower_id' => $this->user->id]);
        $followersFollower = factory('App\Follower')->create(['follower_id' => $usersFollower->user_id]);
    	$post = factory('App\Post')->create(['owner_id' => $followersFollower->user_id]);

    	$response = $this->getJson('/api/discover-posts')->json();

    	$this->assertEquals([$post->id], array_column($response['data'], 'id'));
    }

    /**
     * @test
     * it returns posts from unrelated users to the authenticated user
     */
    public function it_returns_posts_from_unrelated_users_to_the_authenticated_user()
    {
        $this->signIn();
    	$post = factory('App\Post')->create();

    	$response = $this->getJson('/api/discover-posts')->json();

    	$this->assertEquals([$post->id], array_column($response['data'], 'id'));
    }

    /**
     * @test
     * it does not include authenticated users posts
     */
    public function it_does_not_include_authenticated_users_posts()
    {
        $this->signIn();
    	$post = factory('App\Post')->create(['owner_id' => $this->user->id]);

    	$response = $this->getJson('/api/discover-posts')->json();

    	$this->assertNotEquals([$post->id], array_column($response['data'], 'id'));
    }

    /**
     * @test
     * it can correctly calculate a post's like score
     */
    public function it_can_correctly_calculate_a_posts_like_score()
    {
        //arrange
        $total_likes = 10;
        $post = factory('App\Post')->create(['like_count' => $total_likes]);
    
        //act
        $response = $this->getJson('/api/discover-posts');

        //assert
        $this->assertEquals($this->calculateLikeScore($total_likes) , $response->json()['data'][0]['score']);
    }

    /**
     * @test
     * it can correctly calculate a post's chronological score
     */
    public function it_can_correctly_calculate_a_posts_chronological_score()
    {
        //arrange
        $hours_old = 10;
        $post = factory('App\Post')->create(['created_at' => Carbon::now()->subHours($hours_old)]);
    
        //act
        $response = $this->getJson('/api/discover-posts');
    
        //assert
        $this->assertEquals( $this->calculateChronologicalScore($hours_old) , $response->json()['data'][0]['score']);
    }

    /**
     * @test
     * it can correctly calculate a post's pin score
     */
    public function it_can_correctly_calculate_a_posts_pin_score()
    {
        //arrange
        $total_pins = 10;
        $post = factory('App\Post')->create(['pin_count' => $total_pins]);
    
        //act
        $response = $this->getJson('/api/discover-posts');

        //assert
        $this->assertEquals($this->calculatePinScore($total_pins) , $response->json()['data'][0]['score']);
    }

    /**
     * @test
     * it can correctly calculate a post's comment score
     */
    public function it_can_correctly_calculate_a_posts_comment_score()
    {
        //arrange
        $total_comments = 10;
        $post = factory('App\Post')->create(['comment_count' => $total_comments]);
    
        //act
        $response = $this->getJson('/api/discover-posts');

        //assert
        $this->assertEquals($this->calculateCommentScore($total_comments) , $response->json()['data'][0]['score']);
    }

    /**
     * @test
     * it returns posts sorted chronologically
     */
    public function it_returns_posts_sorted_chronologically()
    {
        $old_post_x_hours_old = 2;
        $recent_post_x_hours_old = 1;

    	$old_post = factory('App\Post')->create(['created_at' => Carbon::now()->subHours($old_post_x_hours_old)]);
    	$recent_post = factory('App\Post')->create(['created_at' => Carbon::now()->subHours($recent_post_x_hours_old)]);

    	$response = $this->getJson('/api/discover-posts')->json();

    	// $this->assertEquals([$recent_post->id, $old_post->id], array_column($response['data'], 'id'));
    	$this->assertEquals(
    		[ $this->calculateChronologicalScore($recent_post_x_hours_old), $this->calculateChronologicalScore($old_post_x_hours_old) ],
    		array_column($response['data'], 'score')
		);
    }

    /**
     * @test
     * it returns posts sorted by like count
     */
    public function it_returns_posts_sorted_by_like_count()
    {
    	$postWithTwoLike = factory('App\Post')->create(['like_count' => 2]);
    	$postWithFiveLike = factory('App\Post')->create(['like_count' => 5]);

    	$response = $this->getJson('/api/discover-posts')->json();

    	$this->assertEquals([$postWithFiveLike->id, $postWithTwoLike->id], array_column($response['data'], 'id'));
        $this->assertEquals( [$this->calculateLikeScore(5), $this->calculateLikeScore(2)] ,array_column($response['data'], 'score') );
    }

    /**
     * @test
     * it returns posts sorted by pin count
     */
    public function it_returns_posts_sorted_by_pin_count()
    {
    	$postWithTwoPins = factory('App\Post')->create(['pin_count' => 2]);
    	$postWithFivePins = factory('App\Post')->create(['pin_count' => 5]);

    	$response = $this->getJson('/api/discover-posts')->json();

    	$this->assertEquals([$postWithFivePins->id, $postWithTwoPins->id], array_column($response['data'], 'id'));
        $this->assertEquals( [$this->calculatePinScore(5), $this->calculatePinScore(2)] ,array_column($response['data'], 'score') );
    }

    /**
     * @test
     * it does not return posts with is_undiscoverable set to true
     */
    public function it_does_not_return_posts_with_undiscoverable_set_to_true()
    {
        //when we have a post with is_undiscoverable to true of a unrelated user
        $post = factory('App\Post')->create(['is_undiscoverable' => true]);
        //then if we hit the discover post endpoint
        $response = $this->getJson('/api/discover-posts')->json();
        //the post is not shown
        $this->assertNotEquals([$post->id], array_column($response['data'], 'id'));
    }

    /**
     * returns the chronological score depending on the no of hours
     * @param  number $hours no of hours ago the poat was posted
     * @return float       [description]
     */
    public function calculateChronologicalScore($hours)
    {
        return (new DiscoverPostsController())->calculateChronologicalScore($hours);
    }

    /**
     * returns the like score 
     * @param  int $likes no of likes
     * @return float        [description]
     */
    public function calculateLikeScore($likes)
    {
        return (new DiscoverPostsController())->calculateLikeScore($likes);
    }

    /**
     * returns the pin score 
     * @param  int $pins no of pins
     * @return float        [description]
     */
    public function calculatePinScore($pins)
    {
        return (new DiscoverPostsController())->calculatePinScore($pins);
    }

    /**
     * returns the comment score 
     * @param  int $pins no of pins
     * @return float        [description]
     */
    public function calculateCommentScore($comments)
    {
        return (new DiscoverPostsController())->calculateCommentScore($comments);
    }

    /**
     * @test
     * it returns category images for other discoverable post types like trending selected sale and arteprize
     */
    public function it_returns_category_images_for_other_discoverable_post_types_like_trending_selected_sale_and_arteprize()
    {
        //arrange
        $trending_post = factory('App\Post')->create(['like_count' => 10, 'comment_count' => 10]);
        $arteprize_post = factory('App\Post')->create(['description' => '#artePrize2017']);
        $artevue_selected_post = factory('App\Post')->create(['is_selected_by_artevue' => 1]);
        $on_sale_post = factory('App\Post')->create(['is_selected_for_sale' => 1]);

        //act
        $response = $this->getJson('/api/discover-posts');

        //assert
        $this->assertArrayHasKey('category_images', $response->json());
        $response->assertJsonFragment([
            'trending' => $trending_post->image,
            'selected' => $artevue_selected_post->image,
            'sale' => $on_sale_post->image,
            'arteprize' => $arteprize_post->image
        ]);
    }
}

<?php

namespace Tests\Feature;

use App\Post;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class DiscoverPostTest extends TestCase
{
	use DatabaseTransactions;

    protected $constant_x = 72;  //a post with 1 like and 72 hours old is equivalent in score to a post that has been recently created
	protected $weights = [
		'chronological' => 1.75,
		'like_count' => .30,
		'pin_count' => .10
	];
    
    /**
     * @test
     * it returns paginated json data
     */
    public function it_returns_paginated_json_data()
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
     * it returns posts sorted chronologically
     */
    public function it_returns_posts_sorted_chronologically()
    {
        $old_post_x_hours_old = 2;
        $recent_post_x_hours_old = 1;

        $sho = factory('App\User')->create(['id'=> 33]); // Sho (user id = 33) is one of the user in auto follower list
        $shosFollower = factory('App\Follower')->create(['follower_id' => $sho->id]);

    	$old_post = factory('App\Post')->create(['owner_id' => $shosFollower->user_id, 'created_at' => Carbon::now()->subHours($old_post_x_hours_old)]);
    	$recent_post = factory('App\Post')->create(['owner_id' => $shosFollower->user_id, 'created_at' => Carbon::now()->subHours($recent_post_x_hours_old)]);

    	$response = $this->getJson('/api/discover-posts')->json();

    	$this->assertEquals([$recent_post->id, $old_post->id], array_column($response['data'], 'id'));
    	$this->assertEquals(
    		[ $this->calcutateChronologicalScore($recent_post_x_hours_old), $this->calcutateChronologicalScore($old_post_x_hours_old) ],
    		array_column($response['data'], 'score')
		);
    }

    /**
     * @test
     * it returns posts sorted by like count
     */
    public function it_returns_posts_sorted_by_like_count()
    {
    	$sho = factory('App\User')->create(['id'=> 33]); // Sho (user id = 33) is one of the user in auto follower list
        $shosFollower = factory('App\Follower')->create(['follower_id' => $sho->id]);

    	$postWithTwoLike = factory('App\Post')->create(['owner_id' => $shosFollower->user_id, 'like_count' => 2]);
    	$postWithFiveLike = factory('App\Post')->create(['owner_id' => $shosFollower->user_id, 'like_count' => 5]);

    	$response = $this->getJson('/api/discover-posts')->json();

    	$this->assertEquals([$postWithFiveLike->id, $postWithTwoLike->id], array_column($response['data'], 'id'));
    	$this->assertScoreEquals(5*$this->weights['like_count'], 2*$this->weights['like_count'], $response);
    }

    /**
     * @test
     * it returns posts sorted by pin count
     */
    public function it_returns_posts_sorted_by_pin_count()
    {
    	$sho = factory('App\User')->create(['id'=> 33]); // Sho (user id = 33) is one of the user in auto follower list
        $shosFollower = factory('App\Follower')->create(['follower_id' => $sho->id]);

    	$postWithTwoPins = factory('App\Post')->create(['owner_id' => $shosFollower->user_id, 'pin_count' => 2]);
    	$postWithFivePins = factory('App\Post')->create(['owner_id' => $shosFollower->user_id, 'pin_count' => 5]);

    	$response = $this->getJson('/api/discover-posts')->json();

    	$this->assertEquals([$postWithFivePins->id, $postWithTwoPins->id], array_column($response['data'], 'id'));
    	$this->assertScoreEquals(5*$this->weights['pin_count'], 2*$this->weights['pin_count'], $response);
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

    protected function assertScoreEquals($post_one_score, $post_two_score, $response){
    	$this->assertEquals(
    		[$this->calcutateChronologicalScore(1) + $post_one_score,$this->calcutateChronologicalScore(1) + $post_two_score ],
    		array_column($response['data'], 'score')
		);
    }

    /**
     * returns the chronological weight of a post
     * @param  number $hours no of hours ago the poat was posted
     * @return [type]        [description]
     */
    public function calcutateChronologicalScore($hours)
    {
        return (1/($hours / $this->constant_x))*$this->weights['chronological'];
    }
}

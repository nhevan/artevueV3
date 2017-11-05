<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class TrendingPostsTest extends TestCase
{
    use DatabaseTransactions;

    protected $weights;

    public function setUp()
    {
        parent::setUp();

        $this->seed('SettingsTableSeeder');
        // $this->weights = DiscoverPostsController::getPostWeightDistributionSettings();
    }

    /**
     * @test
     * it returns paginated json data
     */
    public function it_returns_paginated_json_data()
    {
    	$response = $this->getJson('/api/posts/trending')->json();

    	$this->assertArrayHasKey('data', $response);
    	$this->assertArrayHasKey('pagination', $response);
    }

    /**
     * @test
     * it returns post in descending order of like_count
     */
    public function it_returns_post_in_descending_order_of_like_count()
    {
    	//arrange
        $post1 = factory('App\Post')->create(['like_count' => 10]);
        $post2 = factory('App\Post')->create(['like_count' => 15]);
        $post3 = factory('App\Post')->create(['like_count' => 8]);
    
        //act
    	$response = $this->getJson('/api/posts/trending')->json();
    
        //assert
        $this->assertEquals([$post2->id, $post1->id, $post3->id], array_column($response['data'], 'id'));
    }

    /**
     * @test
     * it returns post in descending order of comment count
     */
    public function it_returns_post_in_descending_order_of_comment_count()
    {
    	//arrange
        $post1 = factory('App\Post')->create(['comment_count' => 10]);
        $post2 = factory('App\Post')->create(['comment_count' => 15]);
        $post3 = factory('App\Post')->create(['comment_count' => 8]);
    
        //act
    	$response = $this->getJson('/api/posts/trending')->json();
    
        //assert
        $this->assertEquals([$post2->id, $post1->id, $post3->id], array_column($response['data'], 'id'));
    }
}

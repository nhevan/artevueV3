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

    	$response = $this->getJson('/api/arteprize-posts')->json();

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
    	$response = $this->getJson('/api/arteprize-posts')->json();
    
        //assert
        $this->assertEquals([ $post_recent->id, $post_old->id ], array_column($response['data'], 'id'));
    }
}

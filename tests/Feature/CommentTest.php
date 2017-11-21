<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CommentTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @test
     * an authenticated user can comment on a post
     */
    public function an_authenticated_user_can_comment_on_a_post()
    {
    	//arrange
        $this->signIn();
        $post = factory('App\Post')->create();
    
        //act
    	$response = $this->post("/api/comment/{$post->id}", [
    			'comment' => 'a test comment'
    		]);
    
        //assert
        $response->assertStatus(200);
        $this->assertDatabaseHas('comments', [
        		'user_id' => $this->user->id,
        		'post_id' => $post->id,
        		'comment' => 'a test comment'
        	]);
    }

    /**
     * @test
     * a comment owner can delete his comment on a post
     */
    public function a_comment_owner_can_delete_his_comment_on_a_post()
    {
    	//arrange
        $this->signIn();
        $post = factory('App\Post')->create();
        $comment = factory('App\Comment')->create([
	        	'post_id' => $post->id,
	        	'user_id' => $this->user->id
        	]);
    
        //act
    	$this->delete('/api/comment/'.$comment->id);
    
        //assert
        $this->assertDatabaseMissing('comments', [
        		'user_id' => $this->user->id,
        		'post_id' => $post->id
        	]);
    }

    /**
     * @test
     * a user can fetch all comments on a given post
     */
    public function a_user_can_fetch_all_comments_on_a_given_post()
    {
    	//arrange
        $post = factory('App\Post')->create();
        $comment = factory('App\Comment', 10)->create([
	        	'post_id' => $post->id
        	]);
    
        //act
    	$response = $this->get("/api/comment/{$post->id}");
    
        //assert
        $response->assertJsonFragment([
        		'total' => 10
        	]);
    }
}

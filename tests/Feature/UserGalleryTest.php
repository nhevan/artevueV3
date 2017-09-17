<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class UserGalleryV2ApiTest extends TestCase
{
    use DatabaseTransactions, WithoutMiddleware;
    protected $user;

    public function setUp()
    {
        parent::setUp();

        $user = factory('App\User')->create();
        $user_metadata = factory('App\UserMetadata')->create(['user_id' => $user->id]);
        $this->be($user);

        $this->user = $user;
    }

    /**
     * @test
     * a user a can pin a post to gallery
     */
    public function a_user_a_can_pin_a_post_to_gallery()
    {
    	//arrange
        $post_id = factory('App\Post')->create()->id;
    
        //act
    	$response = $this->post("/api/pin/{$post_id}")->json();
    
        //assert
        $this->assertDatabaseHas('pins', ['user_id' => $this->user->id, 'post_id' => $post_id]);
        $this->assertDatabaseHas('posts', ['id' => $post_id, 'is_gallery_item' => 0]);
    }

    /**
     * @test
     * a post is marked is gallery true when a user pins his own post
     */
    public function a_post_is_marked_is_gallery_true_when_a_user_pins_his_own_post()
    {
        //arrange
        $own_post_id = factory('App\Post')->create(['owner_id'=>$this->user->id])->id;
    
        //act
        $response = $this->post("/api/pin/{$own_post_id}")->json();
    
        //assert
        $this->assertDatabaseHas('pins', ['user_id' => $this->user->id, 'post_id' => $own_post_id]);
        // $this->assertDatabaseHas('posts', ['owner_id' => $this->user->id, 'is_gallery_item' => 1]);
    }

    /**
     * @test
     * a user can unpin a post
     */
    public function a_user_can_unpin_a_post()
    {
        //arrange
        $post_id = factory('App\Post')->create()->id;
        $response = $this->post("/api/pin/{$post_id}")->json();

        //act
        $response = $this->delete("/api/pin/{$post_id}")->json();

        //assert
        $this->assertDatabaseMissing('pins', ['user_id' => $this->user->id, 'post_id' => $post_id]);
    }

    /**
     * @test
     * a user can unpin a post that he owns
     */
    public function a_user_can_unpin_a_post_that_he_owns()
    {
        //arrange
        $own_post_id = factory('App\Post')->create(['owner_id'=>$this->user->id])->id;
        $response = $this->post("/api/pin/{$own_post_id}")->json();
        // $this->assertDatabaseHas('posts', ['owner_id' => $this->user->id, 'is_gallery_item' => 1]);

        //act
        $response = $this->delete("/api/pin/{$own_post_id}")->json();
    
        //assert
        $this->assertDatabaseMissing('pins', ['user_id' => $this->user->id, 'post_id' => $own_post_id]);
        // $this->assertDatabaseHas('posts', ['owner_id' => $this->user->id, 'is_gallery_item' => 0]);
    }

    /**
     * @test
     * we can fetch all posts from a users gallery
     */
    public function we_can_fetch_pinned_posts_from_a_users_gallery()
    {
        //arrange
        $post_id = factory('App\Post')->create()->id;

        $this->post("/api/pin/{$post_id}")->json();
    
        //act
        $response = $this->json('GET' ,"/api/gallery/{$this->user->id}");
    
        //assert
        $response->assertJsonFragment([
            'id' => $post_id,
            'is_gallery_item' => 0
        ]);
    }

    /**
     * @test
     * we can fetch posts marked as gallery items within a users gallery 
     */
    public function we_can_fetch_posts_marked_as_gallery_items_within_a_users_gallery()
    {
        //arrange
        $own_post_id = factory('App\Post')->create(['owner_id'=>$this->user->id])->id;
        $response = $this->post("/api/pin/{$own_post_id}")->json();
        
        //act
        $response = $this->json('GET' ,"/api/gallery/{$this->user->id}");

        //assert
        $response->assertJsonFragment([
            'id' => $own_post_id,
            // 'is_gallery_item' => 1
        ]);
    }

    /**
     * @test
     * lock status of a gallery post can be updated if the user is the owner of the post
     */
    public function lock_status_of_a_gallery_post_can_be_updated_if_the_user_is_the_owner_of_the_post()
    {
        //arrange
        $own_post_id = factory('App\Post')->create(['owner_id'=>$this->user->id])->id;
        $this->post("/api/pin/{$own_post_id}")->json();
        $this->assertDatabaseHas('pins', ['user_id' => $this->user->id, 'post_id' => $own_post_id, 'is_locked' => 0]);
    
        //act
        $response = $this->patch("/api/post/{$own_post_id}", ['is_locked' => 1])->json();
    
        //assert
        $this->assertDatabaseHas('pins', ['user_id' => $this->user->id, 'post_id' => $own_post_id, 'is_locked' => 1]);
    }

    /**
     * @test
     * gallery posts are returned with their sequence in ascending order
     */
    public function gallery_posts_are_returned_with_their_sequence_in_ascending_order()
    {
        //arrange
        $sequence0_pin = factory('App\Pin')->create(['user_id'=>$this->user->id]);
        $sequence1_pin = factory('App\Pin')->create(['user_id'=>$this->user->id, 'sequence' => 1]);
        $sequence2_pin = factory('App\Pin')->create(['user_id'=>$this->user->id, 'sequence' => 2]);

        //act
        $response = $this->getJson("/api/gallery/{$this->user->id}")->json();

        //assert
        $this->assertEquals([ $sequence0_pin->post_id, $sequence1_pin->post_id, $sequence2_pin->post_id ], array_column($response['data'], 'id'));
    }

    /**
     * @test
     * gallery posts can be arranged in any given order
     */
    public function gallery_posts_can_be_arranged_in_any_given_order()
    {
        //arrange
        $sequence1_pin = factory('App\Pin')->create(['user_id'=>$this->user->id]);
        $sequence2_pin = factory('App\Pin')->create(['user_id'=>$this->user->id]);
        $sequence3_pin = factory('App\Pin')->create(['user_id'=>$this->user->id]);

        //act
        $request_body = [
            'posts' => [
                [
                    'id' => $sequence1_pin->post_id,
                    'owner' => [
                        'id' => 1
                    ]
                ],
                [
                    'id' => $sequence2_pin->post_id,
                    'owner' => [
                        'id' => 1
                    ]
                ],
                [
                    'id' => $sequence3_pin->post_id,
                    'owner' => [
                        'id' => $this->user->id
                    ]
                ]
            ]
        ];

        $response = $this->post("/api/arrange-gallery", $request_body)->json();
    
        //assert
        $this->assertDatabaseHas('pins', ['id' => $sequence1_pin->id, 'sequence' => 1]);
        $this->assertDatabaseHas('pins', ['id' => $sequence2_pin->id, 'sequence' => 2]);
        $this->assertDatabaseHas('pins', ['id' => $sequence3_pin->id, 'sequence' => 3]);
    }

}

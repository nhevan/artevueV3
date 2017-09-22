<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class UserGalleryV3ApiTest extends TestCase
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
     * a user can create a gallery with a name, description, email and a website
     */
    public function a_user_can_create_a_gallery_with_a_name_description_email_and_a_website()
    {
    	//arrange
    	$gallery = factory('App\Gallery')->raw(['user_id' => $this->user->id]);
    
        //act
    	$response = $this->json( 'POST', "/api/gallery", $gallery);

    	//assert
    	$response->assertStatus(201);
    	$response->assertJsonFragment([
    		'gallery_id'
		]);
        $this->assertDatabaseHas('galleries', ['user_id' => $this->user->id, 'name' => $gallery['name']]);
    }

    /**
     * @test
     * a user can not have 2 galleries of the same name
     */
    public function a_user_can_not_have_2_galleries_of_the_same_name()
    {
    	//arrange
        $gallery1 = factory('App\Gallery')->raw(['user_id' => $this->user->id, 'name' => 'Same name for both galleries.']);
        $gallery2 = factory('App\Gallery')->raw(['user_id' => $this->user->id, 'name' => 'Same name for both galleries.']);
    
        //act
    	$this->json( 'POST', "/api/gallery", $gallery1);
    	$response = $this->json( 'POST', "/api/gallery", $gallery2);

        //assert
        $response->assertStatus(422);
    }

    /**
     * @test
     * it can return all galleries under a given user
     */
    public function it_can_return_all_galleries_under_a_given_user()
    {
    	//arrange
        $gallery = factory('App\Gallery')->create(['user_id' => $this->user->id]);
        $gallery2 = factory('App\Gallery')->create(['user_id' => $this->user->id]);
    	$pins = factory('App\Pin', 6)->create(['gallery_id'=>$gallery->id]);

        //act
    	$response = $this->json( 'GET', "/api/user/{$this->user->id}/galleries");

        //assert
		$response->assertJsonFragment([
			'id' => $gallery->id
		]);
		$response->assertJsonFragment([
			'id' => $gallery2->id
		]);
    }

    /**
     * @test
     * it returns galleries according to sequence 
     */
    public function it_returns_galleries_according_to_sequence()
    {
    	//arrange
        $gallery = factory('App\Gallery')->create(['user_id' => $this->user->id, 'sequence' => 2]);
        $gallery2 = factory('App\Gallery')->create(['user_id' => $this->user->id, 'sequence' => 3]);
    	$new_gallery = factory('App\Gallery')->create(['user_id' => $this->user->id]);

        //act
    	$response = $this->getJson("/api/user/{$this->user->id}/galleries")->json();
    
        //assert
        $this->assertEquals([$new_gallery->id, $gallery->id, $gallery2->id], array_column($response['data'], 'id'));
    }

    /**
     * @test
     * it can return all posts within a gallery
     */
    public function it_can_return_all_posts_within_a_gallery()
    {
    	//arrange
        $gallery = factory('App\Gallery')->create(['user_id' => $this->user->id]);
    	$pins = factory('App\Pin', 6)->create(['gallery_id'=>$gallery->id])->sortByDesc('id');

        //act
    	$response = $this->getJson("/api/user/{$this->user->id}/gallery/{$gallery->id}")->json();
        //assert
        $this->assertEquals($pins->pluck('id')->all(), array_column($response['data'] ,'id'));
    }

    /**
     * @test
     * a gallery owner can rename a gallery
     */
    public function a_gallery_owner_can_rename_a_gallery()
    {
    	//arrange
        $gallery = factory('App\Gallery')->create(['user_id' => $this->user->id, 'name' => 'gallery name']);
    
        //act
    	$response = $this->json( 'PATCH', "/api/gallery/{$gallery->id}", ['name'=>'edited gallery name']);
    
        //assert
		$this->assertDatabaseHas('galleries', ['id' => $gallery->id, 'name' => 'edited gallery name']);        
    }

    /**
     * @test
     * while renaming a gallery the gallery name must be unique
     */
    public function while_renaming_a_gallery_the_gallery_name_must_be_unique()
    {
    	//arrange
        $gallery1 = factory('App\Gallery')->create(['user_id' => $this->user->id, 'name' => 'gallery one']);
        $gallery2 = factory('App\Gallery')->create(['user_id' => $this->user->id, 'name' => 'gallery two']);
    
        //act
    	$response = $this->json( 'PATCH', "/api/gallery/{$gallery2->id}", ['name'=>'gallery two']);

        //assert
        $response->assertStatus(422);
    }

    /**
     * @test
     * a user can sort their galleries according to a given set of gallery ids
     */
    public function a_user_can_sort_their_galleries_according_to_a_given_set_of_gallery_ids()
    {
    	//arrange
        $gallery1 = factory('App\Gallery')->create(['user_id' => $this->user->id]);
        $gallery2 = factory('App\Gallery')->create(['user_id' => $this->user->id]);
    	$gallery3 = factory('App\Gallery')->create(['user_id' => $this->user->id]);

        //act
    	$response = $this->json('PATCH', "/api/user/{$this->user->id}/galleries", ['sequence' => [$gallery2->id, $gallery3->id, $gallery1->id ] ])->json();
    	
        //assert
        $this->assertDatabaseHas('galleries', ['id' => $gallery2->id, 'sequence' => 1]);
        $this->assertDatabaseHas('galleries', ['id' => $gallery3->id, 'sequence' => 2]);
        $this->assertDatabaseHas('galleries', ['id' => $gallery1->id, 'sequence' => 3]);
    }

    /**
     * @test
     * while fetching gallery lists it returns first 4 pins along with each gallery
     */
    public function while_fetching_gallery_lists_it_returns_first_4_pins_along_with_each_gallery()
    {
    	//arrange
        $gallery1 = factory('App\Gallery')->create(['user_id' => $this->user->id]);
        $gallery2 = factory('App\Gallery')->create(['user_id' => $this->user->id]);
    	$pins = factory('App\Pin', 3)->create(['gallery_id'=>$gallery1->id, 'user_id' => $this->user->id])->sortBy('sequence');
    	$post = factory('App\Post')->create();
    	$custom_pin = factory('App\Pin')->create([ 'gallery_id' => $gallery1->id, 'user_id' => $this->user->id, 'post_id' => $post->id ]);
    	$pins_gallery2 = factory('App\Pin', 3)->create(['gallery_id'=>$gallery2->id, 'user_id' => $this->user->id])->sortByDesc('id');
    	$custom_pin_2 = factory('App\Pin')->create([ 'gallery_id' => $gallery2->id, 'user_id' => $this->user->id, 'post_id' => $post->id ]);

        //act
    	$response = $this->json('GET', "/api/user/{$this->user->id}/galleries")->json();

    	// dd($response);
    
        //assert
        $this->assertDatabaseHas('pins', ['gallery_id' => $gallery1->id, 'user_id' => $this->user->id, 'post_id' => $post->id]);
        $this->assertDatabaseHas('pins', ['gallery_id' => $gallery2->id, 'user_id' => $this->user->id, 'post_id' => $post->id]);

        $this->assertEquals([ $gallery1->id, $gallery2->id ] , array_column($response['data'] ,'id'));
        $this->assertEquals(4, sizeof(array_column( array_column($response['data'] ,'first_four_pins')[0], 'id')));
        $this->assertEquals(4, sizeof(array_column( array_column($response['data'] ,'first_four_pins')[1], 'id')));
        $this->assertEquals( array_merge($pins->pluck('id')->all(), [$custom_pin->id]) , array_column( array_column($response['data'] ,'first_four_pins')[0], 'id'));
    }

    /**
     * @test
     * while fetching a single gallery it fetches all pins within that gallery
     */
    public function while_fetching_a_single_gallery_it_fetches_all_pins_within_that_gallery()
    {
    	//arrange
    	$gallery1 = factory('App\Gallery')->create(['user_id' => $this->user->id]);
    	$gallery2 = factory('App\Gallery')->create(['user_id' => $this->user->id]);
    	$pins = factory('App\Pin', 3)->create(['gallery_id'=>$gallery1->id, 'user_id' => $this->user->id])->sortByDesc('id');
    	$post = factory('App\Post')->create();
    	$custom_pin = factory('App\Pin')->create([ 'gallery_id' => $gallery1->id, 'user_id' => $this->user->id, 'post_id' => $post->id ]);
    	$custom_pin_2 = factory('App\Pin')->create([ 'gallery_id' => $gallery2->id, 'user_id' => $this->user->id, 'post_id' => $post->id ]);
        
        //act
		$response = $this->json('GET', "/api/user/{$this->user->id}/gallery/{$gallery1->id}")->json();    	

        //assert
        $this->assertEquals( array_merge([$custom_pin->id], $pins->pluck('id')->all()), array_column($response['data'] ,'id'));
    }
}

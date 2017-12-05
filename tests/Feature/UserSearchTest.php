<?php

namespace Tests\Feature;

use App\UserType;
use Tests\SearchTestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class UserSearchTest extends SearchTestCase
{
	protected $className = 'User';
	protected $plural = 'users';

	public function setUpTestClassInfo()
	{
		return [
			'className' => $this->className,
			'plural' => $this->plural
		];
	}

	public function setUp()
    {
        parent::setUp();

        $this->seed('UserTypesTableSeeder');
    }
    /**
     * @test
     * users can be searched by matching username
     */
    public function users_can_be_searched_by_matching_username()
    {
    	//arrange
        $needle = factory('App\User')->create(['username'=> $this->matches_needle_string]);
        factory('App\UserMetadata')->create(['user_id'=>$needle->id]);
        $users = factory('App\User', 4)->create();
    
        //act
        $this->search($needle)->matchByField('username')->checkSingularity();
    	
    }

    /**
     * @test
     * users can be searched by matching email
     */
    public function users_can_be_searched_by_matching_email()
    {
    	//arrange
        $needle = factory('App\User')->create(['email'=> $this->matches_needle_string]);
        factory('App\UserMetadata')->create(['user_id'=>$needle->id]);
        $users = factory('App\User', 4)->create();
    
        //act
        $this->search($needle)->matchByField('email')->checkSingularity();
    	
    }

    /**
     * @test
     * a user can be searched by matching name case sensitively
     */
    public function a_user_can_be_searched_by_matching_name_case_sensitively()
    {
    	//arrange
        $needle = factory('App\User')->create(['name'=> $this->matches_needle_string]);
        factory('App\UserMetadata')->create(['user_id'=>$needle->id]);
        $users = factory('App\User', 4)->create();
    
        //act
        $this->search($needle)->matchByField('name')->checkSingularity();
    }

    /**
     * @test
     * a user can be searched by user type
     */
    public function a_user_can_be_searched_by_user_type()
    {
        //arrange
        $needle = factory('App\User')->create();
        factory('App\UserMetadata')->create(['user_id'=>$needle->id]);
        $users = factory('App\User', 4)->create();
    
        //act
        $this->search($needle)->equalityByField('user_type_id', $needle->user_type_id, 1)->checkSingularity();
    }

    /**
     * @test
     * users can be searched by multiple user types
     */
    public function users_can_be_searched_by_multiple_user_types()
    {
        //arrange
        $collector = factory('App\User')->create(['user_type_id' => 3]);
        factory('App\UserMetadata')->create(['user_id'=>$collector->id]);
        $gallery = factory('App\User')->create(['user_type_id' => 4]);
        factory('App\UserMetadata')->create(['user_id'=>$gallery->id]);
        $enthusiast = factory('App\User')->create(['user_type_id' => 5]);
        factory('App\UserMetadata')->create(['user_id'=>$enthusiast->id]);
        
        //act
        $response = $this->json( 'GET', "/api/search-users", [
            'user_type_id' => "3,4"
        ]);
    
        //assert
        $response->assertJsonFragment([
            'id' => $collector->id,
            'id' => $gallery->id,
        ]);
        $response->assertJsonMissing([
            'id' => $enthusiast->id
        ]);
    }

    /**
     * @test
     * users are returned in descending order of activity count
     */
    public function users_are_returned_in_descending_order_of_activity_count()
    {
        //arrange
        $last = factory('App\UserMetadata')->create([
            'post_count' => 5,
            'follower_count' => 5,
            'like_count' => 5
        ]);
        $second = factory('App\UserMetadata')->create([
            'post_count' => 5,
            'follower_count' => 10,
            'like_count' => 10
        ]);
        $first = factory('App\UserMetadata')->create([
            'post_count' => 10,
            'follower_count' => 10,
            'like_count' => 10
        ]);
        $unrelated = factory('App\UserMetadata')->create();

        //act
        $response = $this->json( 'GET', "/api/search-users", [
            'user_type_id' => "{$last->user->user_type_id},{$second->user->user_type_id},{$first->user->user_type_id}"
        ]);
        
        //assert
        $this->assertEquals([$first->user_id, $second->user_id, $last->user_id], array_column($response->json()['data'], 'id'));
        $response->assertJsonMissing([
            'id' => $unrelated->user_id
        ]);
    }

    /**
     * @test
     * when a logged in user searches other users the returned is_following key is correct
     */
    public function when_a_logged_in_user_searches_other_users_the_returned_is_following_key_is_correct()
    {
        //arrange
        $this->signIn();
        $random_user = factory('App\User')->create(['user_type_id' => 6]);
        factory('App\UserMetadata')->create(['user_id' => $random_user->id]);
        
        //act
        $follow = $this->post('api/follow/'.$random_user->id);
        $response = $this->get('/api/search-users', ['user_type_id' => 6]);

        //assert
        $response->assertJsonFragment([
            'is_following' => true
        ]);
    }
}

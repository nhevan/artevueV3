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
        $gallery = factory('App\User')->create(['user_type_id' => 4]);
        $enthusiast = factory('App\User')->create(['user_type_id' => 5]);
        
        //act
        $response = $this->json( 'GET', "/api/search-users", [
            'user_type_id' => "3,4"
        ]);
    
        //assert
        $response->assertJsonFragment([
            'id' => $collector->id
        ]);
        $response->assertJsonFragment([
            'id' => $gallery->id
        ]);
        $response->assertJsonMissing([
            'id' => $enthusiast->id
        ]);
    }
}

<?php

namespace Tests\Feature;

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
}

<?php

namespace Tests\Feature;

use Tests\SearchTestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class UserSearchTest extends SearchTestCase
{
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
    	$response = $this->json( 'GET', "/api/search-users", [
 				'username' => $this->needle_string
			]);
 	
 	    //assert
 	    $response->assertJsonFragment([
 	    		'id' => $needle->id,
 	    		'username' => $this->matches_needle_string
 	    	]);
 	    $this->checkSingularity($response);
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
    	$response = $this->json( 'GET', "/api/search-users", [
 				'name' => strtoupper($this->needle_string)
			]);

 	    //assert
 	    $response->assertJsonFragment([
 	    		'id' => $needle->id,
 	    		'name' => $this->matches_needle_string
 	    	]);
 	   	$this->checkSingularity($response);
    }
}

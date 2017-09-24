<?php

namespace Tests\Feature;

use Tests\SearchTestCase;

class PostSearchTest extends SearchTestCase
{
 	/**
 	 * @test
 	 * posts can be searched by matching description
 	 */
 	public function posts_can_be_searched_by_matching_description()
 	{
 		//arrange
 	    $needle = factory('App\Post')->create(['description' => $this->matches_needle_string]);
 		$posts = factory('App\Post', 4)->create();

 	    //act
 		$response = $this->json( 'GET', "/api/search-posts", [
 				'description' => $this->needle_string
			]);
 	
 	    //assert
 	    $response->assertJsonFragment([
 	    		'id' => $needle->id
 	    	]);

 	    $this->checkSingularity($response);
 	}

 	/**
 	 * @test
 	 * posts can be searched my minimum price
 	 */
 	public function posts_can_be_searched_my_minimum_price()
 	{
 		//arrange
 	    $needle = factory('App\Post')->create(['price' => $this->needle_int]);
	 	$posts = factory('App\Post', 4)->create(['price' => $this->less_than_needle_int]);

 	    //act
 		$response = $this->json( 'GET', "/api/search-posts", [
 				'minimum_price' => $this->needle_int
			]);
 	
 	    //assert
 	    $response->assertJsonFragment([
 	    		'id' => $needle->id
 	    	]);

 	    $this->checkSingularity($response);
 	}
}

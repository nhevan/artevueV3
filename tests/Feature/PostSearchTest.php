<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PostSearchTest extends TestCase
{
 	use DatabaseTransactions;

 	/**
 	 * @test
 	 * posts can be searched by matching description
 	 */
 	public function posts_can_be_searched_by_matching_description()
 	{
 		//arrange
 	    $needle = factory('App\Post')->create(['description' => 'description clue']);
 		$posts = factory('App\Post', 4)->create();

 	    //act
 		$response = $this->json( 'GET', "/api/search-posts", [
 				'description' => 'clue'
			]);
 	
 	    //assert
 	    $response->assertJsonFragment([
 	    		'id' => $needle->id
 	    	]);
 	}

 	/**
 	 * @test
 	 * posts can be searched my minimum price
 	 */
 	public function posts_can_be_searched_my_minimum_price()
 	{
 		//arrange
 	    $needle = factory('App\Post')->create(['price' => '100']);
	 	$posts = factory('App\Post', 4)->create(['price' => '50']);

 	    //act
 		$response = $this->json( 'GET', "/api/search-posts", [
 				'minimum_price' => '100'
			]);
 	
 	    //assert
 	    $response->assertJsonFragment([
 	    		'id' => $needle->id
 	    	]);
 	}
}

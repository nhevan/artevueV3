<?php

namespace Tests\Feature;

use Tests\SearchTestCase;

class PostSearchTest extends SearchTestCase
{
	protected $className = 'Post';
	protected $plural = 'posts';

	public function setUpTestClassInfo()
	{
		return [
			'className' => $this->className,
			'plural' => $this->plural
		];
	}
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
 	    $this->search($needle)->matchByField('description')->checkSingularity();
 	}

 	/**
 	 * @test
 	 * posts can be searched my minimum price
 	 */
 	public function posts_can_be_searched_my_min_price()
 	{
 		//arrange
 	    $needle = factory('App\Post')->create(['price' => $this->needle_int]);
	 	$posts = factory('App\Post', 4)->create(['price' => $this->less_than_needle_int]);

 	    //act
 	    $this->search($needle)->equalityByField('price', $needle->price, 1, 'min_price')->checkSingularity();
 	}

 	/**
 	 * @test
 	 * posts can be searched my maximum price
 	 */
 	public function posts_can_be_searched_my_max_price()
 	{
 		//arrange
 	    $needle = factory('App\Post')->create(['price' => $this->less_than_needle_int]);
	 	$posts = factory('App\Post', 4)->create(['price' => $this->needle_int]);

 	    //act
 	    $this->search($needle)->equalityByField('price', $needle->price, 1, 'max_price')->checkSingularity();
 	}

 	/**
     * @test
     * it can search posts by any one the listed fields
     */
    public function it_can_search_posts_by_any_one_the_listed_fields()
    {
    	//arrange
 	    $needle = factory('App\Post')->create(['hashtags' => $this->matches_needle_string]);
	 	$posts = factory('App\Post', 4)->create();

 	    //act
 	    $this->search($needle)->matchByField('hashtags')->checkSingularity();
    }

    /**
     * @test
     * posts can be searched by owner username
     */
    public function posts_can_be_searched_by_owner_username()
    {
    	//arrange
    	$needle = factory('App\Post')->create();
        $posts = factory('App\Post', 4)->create();
    
        //act
        $this->search($needle)->matchByField('username', $needle->owner->username, 'owner_username');
    }

    /**
     * @test
     * private posts are never returned
     */
    public function private_posts_are_never_returned()
    {
    	//arrange
        $needle = factory('App\Post')->create(['is_public' => 0]);
        $posts = factory('App\Post', 5)->create(['is_public' => 1]);
    
    	//act
    	$response = $this->json( 'GET', "/api/search-posts")->json();
    
        //assert
        $this->assertEquals(5, $response['pagination']['total']);
        
    }
}

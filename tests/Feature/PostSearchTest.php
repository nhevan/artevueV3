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
 	public function posts_can_be_searched_my_minimum_price()
 	{
 		//arrange
 	    $needle = factory('App\Post')->create(['price' => $this->needle_int]);
	 	$posts = factory('App\Post', 4)->create(['price' => $this->less_than_needle_int]);

 	    //act
 	    $this->search($needle)->equalityByField('price', $needle->price, 1, 'minimum_price')->checkSingularity();
 	}

 	/**
 	 * @test
 	 * posts can be searched my maximum price
 	 */
 	public function posts_can_be_searched_my_maximum_price()
 	{
 		//arrange
 	    $needle = factory('App\Post')->create(['price' => $this->less_than_needle_int]);
	 	$posts = factory('App\Post', 4)->create(['price' => $this->needle_int]);

 	    //act
 	    $this->search($needle)->equalityByField('price', $needle->price, 1, 'maximum_price')->checkSingularity();
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
}

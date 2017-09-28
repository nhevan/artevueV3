<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class DefaultSearchTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @test
     * it returns full Post record set if no specific field is given
     */
    public function it_returns_full_Post_record_set_if_no_specific_field_is_given()
    {
    	//arrange
        $posts = factory('App\Post', 4)->create();
    
        //act
    	$response = $this->json( 'GET', "/api/search-posts")->json();
    
        //assert
        $this->assertEquals(4, $response['pagination']['total']);
    }

    /**
     * @test
     * it returns full User record set if no specific field is given
     */
    public function it_returns_full_User_record_set_if_no_specific_field_is_given()
    {
    	//arrange
        $users = factory('App\User', 4)->create();
    
        //act
    	$response = $this->json( 'GET', "/api/search-users")->json();

        //assert
        $this->assertEquals(4, $response['pagination']['total']);
    }

    /**
     * @test
     * it catches validation error while searching posts
     */
    public function it_catches_validation_errors_while_searching_posts()
    {
        //arrange
        $posts = factory('App\Post', 4)->create();

        //act
    	$response = $this->json( 'GET', "/api/search-posts", [
    			'price' => '-5'
    		]);
    
        //assert
        $this->assertEquals(422, $response->status());
    }

    /**
     * @test
     * it ignores parameters that does not belong to model fields list
     */
    public function it_ignores_parameters_that_does_not_belong_to_model_fields_list()
    {
        //arrange
        $posts = factory('App\Post', 4)->create();
    
        //act
        $response = $this->json( 'GET', "/api/search-posts", [
                'name' => 'test name'
            ]);
    
        //assert
        $this->assertEquals(200, $response->status());
    }

    /**
     * @test
     * it can handle a prefixed wrong column
     */
    public function it_can_handle_a_prefixed_wrong_column()
    {
        //act
        $response = $this->json( 'GET', "/api/search-posts", [
                'min_random_column' => 1
            ]);  
    
        //assert
        $this->assertEquals(200, $response->status());
    }

    /**
     * @test
     * it matches integer fields
     */
    public function it_matches_integer_fields()
    {
        //arrange
        $needle = factory('App\Post')->create(['price' => 10]);
        $posts = factory('App\Post', 4)->create(['price' => 500]);
        factory('App\Post')->create(['price' => 1010]);

        //act
        $response = $this->json( 'GET', "/api/search-posts", [
                'price' => 10
            ]);

        $this->assertEquals(1, $response->json()['pagination']['total']);
        
    }

    /**
     * @test
     * it can search for exact matches for string type fields
     */
    public function it_can_search_for_exact_matches_for_string_type_fields()
    {
        //arrange
        $needle = factory('App\Post')->create(['description' => 'des']);
        $posts = factory('App\Post', 4)->create(['description' => 'desdesdes']);
    
        //act
        $response = $this->json( 'GET', "/api/search-posts", [
                'exact_description' => 'des'
            ]);
    
        //assert
        $this->assertEquals(1, $response->json()['pagination']['total']);
    }

    /**
     * @test
     * it can chain dedicated field methods along with other fields
     */
    public function it_can_chain_dedicated_field_methods_along_with_other_fields()
    {
        //arrange
        $needle = factory('App\Post')->create();
        $posts = factory('App\Post', 4)->create();
    
        //act
        $response = $this->json( 'GET', "/api/search-posts", [
                'description' => $needle->description,
                'image' => $needle->image
            ]);
    
        //assert
        $this->assertEquals(1, $response->json()['pagination']['total']);   
    }

    /**
     * @test
     * it can sort resultset by given sortBy fields
     */
    public function it_can_sort_resultset_by_given_sortBy_fields()
    {
        //arrange
        $med_price = factory('App\Post')->create(['price' => 5]);
        $high_prices = factory('App\Post', 4)->create(['price' => 500]);
        $lowest_price = factory('App\Post')->create(['price' => 3]);
    
        $user_y = factory('App\User')->create(['name' => 'y']);
        $user_x = factory('App\User')->create(['name' => 'x']);
        $user_z = factory('App\User')->create(['name' => 'z']);

        // act
        $search_post_response = $this->json( 'GET', "/api/search-posts", [
                'sort_by_price' => 'asc',
                'some_other_field' => 'random'
            ]);

        $search_user_response = $this->json( 'GET', "/api/search-users", [
                'sort_by_name' => 'desc',
            ]);

        // assert
        $this->assertEquals(3, $search_post_response->json()['data'][0]['price']);
        $this->assertEquals(5, $search_post_response->json()['data'][1]['price']);

        $this->assertEquals('z', $search_user_response->json()['data'][0]['name']);
        $this->assertEquals('y', $search_user_response->json()['data'][1]['name']);
        $this->assertEquals('x', $search_user_response->json()['data'][2]['name']);

        // dd($search_user_response->json());
    }

}

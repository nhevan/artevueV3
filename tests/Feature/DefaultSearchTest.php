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

}

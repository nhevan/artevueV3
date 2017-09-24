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
    	$response = $this->json( 'GET', "/api/search-posts");
    
        //assert
        $this->assertEquals(4, sizeof($response->json()));
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
    	$response = $this->json( 'GET', "/api/search-users");
    
        //assert
        $this->assertEquals(4, sizeof($response->json()));
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

}

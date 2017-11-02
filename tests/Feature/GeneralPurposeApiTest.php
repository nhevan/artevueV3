<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class GeneralPurposeApiTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @test
     * it returns a list of avatar for user types
     */
    public function it_returns_a_list_of_avatar_for_user_types()
    {
    	//arrange
    	$this->seed('UserTypesTableSeeder');
        $artist = factory('App\User')->create([ 'user_type_id' => 6]);
        $gallery = factory('App\User')->create([ 'user_type_id' => 4]);
        $collector = factory('App\User')->create([ 'user_type_id' => 3]);
        $enthusiast = factory('App\User')->create([ 'user_type_id' => 5]);
        $professional = factory('App\User')->create([ 'user_type_id' => 8]);
    
        //act
    	$response = $this->get('/api/user-types/avatars');
    
        //assert
        $response->assertJsonFragment([
        	"artist" => $artist->profile_picture,
        	"gallery" => $gallery->profile_picture,
        	"collector" => $collector->profile_picture,
        	"enthusiast" => $enthusiast->profile_picture,
        	"professional" => $professional->profile_picture,
    	]);
    }
}

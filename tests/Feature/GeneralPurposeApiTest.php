<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class GeneralPurposeApiTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @test
     * avatars endpoint returns 4 avatars for artist type users
     */
    public function avatars_endpoint_returns_4_avatars_for_artist_type_users()
    {
        //arrange
        $this->seed('UserTypesTableSeeder');
        $ignored1 = factory('App\User')->create([ 'user_type_id' => 6, 'profile_picture' => 'dummy.png', 'created_at' => Carbon::now()->subDays(2)]);
        $artist1 = factory('App\User')->create([ 'user_type_id' => 6]);
        $artist2 = factory('App\User')->create([ 'user_type_id' => 6]);
        $artist3 = factory('App\User')->create([ 'user_type_id' => 6]);
        $artist4 = factory('App\User')->create([ 'user_type_id' => 6]);
    
        //act
        $response = $this->get('/api/user-types/avatars');

        //assert
        $response->assertJsonFragment([
            "artist" => [ $artist4->profile_picture, $artist3->profile_picture, $artist2->profile_picture, $artist1->profile_picture ]
        ]);
    }

    /**
     * @test
     * avatars endpoint returns 4 avatars for gallery type users
     */
    public function avatars_endpoint_returns_4_avatars_for_gallery_type_users()
    {
        //arrange
        $this->seed('UserTypesTableSeeder');
        $ignored1 = factory('App\User')->create([ 'user_type_id' => 4, 'profile_picture' => 'dummy.png', 'created_at' => Carbon::now()->subDays(2)]);
        $gallery1 = factory('App\User')->create([ 'user_type_id' => 4]);
        $gallery2 = factory('App\User')->create([ 'user_type_id' => 4]);
        $gallery3 = factory('App\User')->create([ 'user_type_id' => 4]);
        $gallery4 = factory('App\User')->create([ 'user_type_id' => 4]);
    
        //act
        $response = $this->get('/api/user-types/avatars');

        //assert
        $response->assertJsonFragment([
            "gallery" => [ $gallery4->profile_picture, $gallery3->profile_picture, $gallery2->profile_picture, $gallery1->profile_picture ]
        ]);
    }

    /**
     * @test
     * avatars endpoint returns 4 avatars for collector type users
     */
    public function avatars_endpoint_returns_4_avatars_for_collector_type_users()
    {
        //arrange
        $this->seed('UserTypesTableSeeder');
        $ignored1 = factory('App\User')->create([ 'user_type_id' => 3, 'profile_picture' => 'dummy.png', 'created_at' => Carbon::now()->subDays(2)]);
        $collector1 = factory('App\User')->create([ 'user_type_id' => 3]);
        $collector2 = factory('App\User')->create([ 'user_type_id' => 3]);
        $collector3 = factory('App\User')->create([ 'user_type_id' => 3]);
        $collector4 = factory('App\User')->create([ 'user_type_id' => 3]);
    
        //act
        $response = $this->get('/api/user-types/avatars');

        //assert
        $response->assertJsonFragment([
            "collector" => [ $collector4->profile_picture, $collector3->profile_picture, $collector2->profile_picture, $collector1->profile_picture ]
        ]);
    }

    /**
     * @test
     * avatars endpoint returns 4 avatars for enthusiast type users
     */
    public function avatars_endpoint_returns_4_avatars_for_enthusiast_type_users()
    {
        //arrange
        $this->seed('UserTypesTableSeeder');
        $ignored1 = factory('App\User')->create([ 'user_type_id' => 5, 'profile_picture' => 'dummy.png', 'created_at' => Carbon::now()->subDays(2)]);
        $enthusiast1 = factory('App\User')->create([ 'user_type_id' => 5]);
        $enthusiast2 = factory('App\User')->create([ 'user_type_id' => 5]);
        $enthusiast3 = factory('App\User')->create([ 'user_type_id' => 5]);
        $enthusiast4 = factory('App\User')->create([ 'user_type_id' => 5]);
    
        //act
        $response = $this->get('/api/user-types/avatars');

        //assert
        $response->assertJsonFragment([
            "enthusiast" => [ $enthusiast4->profile_picture, $enthusiast3->profile_picture, $enthusiast2->profile_picture, $enthusiast1->profile_picture ]
        ]);
    }

    /**
     * @test
     * avatars endpoint returns 4 avatars for professional type users
     */
    public function avatars_endpoint_returns_4_avatars_for_professional_type_users()
    {
        //arrange
        $this->seed('UserTypesTableSeeder');
        $ignored1 = factory('App\User')->create([ 'user_type_id' => 8, 'profile_picture' => 'dummy.png', 'created_at' => Carbon::now()->subDays(2)]);
        $professional1 = factory('App\User')->create([ 'user_type_id' => 8]);
        $professional2 = factory('App\User')->create([ 'user_type_id' => 8]);
        $professional3 = factory('App\User')->create([ 'user_type_id' => 8]);
        $professional4 = factory('App\User')->create([ 'user_type_id' => 8]);
    
        //act
        $response = $this->get('/api/user-types/avatars');

        //assert
        $response->assertJsonFragment([
            "professional" => [ $professional4->profile_picture, $professional3->profile_picture, $professional2->profile_picture, $professional1->profile_picture ]
        ]);
    }

    /**
     * @test
     * an authenticated user can report another user
     */
    public function an_authenticated_user_can_report_another_user()
    {
        //arrange
        $this->signIn();
        $suspect = factory('App\User')->create();
    
        //act
        $response = $this->post("api/report/{$suspect->id}");
    
        //assert
        $response->assertStatus(200);
        //basically reporting a user does not do anything other than just storing the information on a database table
        $this->assertDatabaseHas('reported_users', [
                'user_id' => $this->user->id,
                'suspect_id' => $suspect->id
            ]);
    }

    /**
     * @test
     * an authenticated user can update his location, latitude and longitude
     */
    public function an_authenticated_user_can_update_his_location_latitude_and_longitude()
    {
        //arrange
        $this->signIn();
    
        //act
        $response = $this->post("api/user/location", [
                'latitude' => '93.125454',
                'longitude' => '23.646646364564',
                'location' => 'London, UK'
            ]);
    
        //assert
        $this->assertDatabaseHas('users', [
                'id' => $this->user->id,
                'latitude' => '93.125454',
                'longitude' => '23.646646364564',
                'location' => 'London, UK'
            ]);
    }
}

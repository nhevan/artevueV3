<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
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
    public function avatars_endpoint_returns_4_avatars_for_top_artist_type_users()
    {
        //arrange
        $this->seed('UserTypesTableSeeder');
        $ignored1 = factory('App\User')->create([ 'user_type_id' => 6, 'profile_picture' => 'dummy.png', 'created_at' => Carbon::now()->subDays(2)]);
        $artist1 = factory('App\User')->create([ 'user_type_id' => 6, 'profile_picture' => 'img/needle-image1.jpg', 'created_at' => Carbon::now()->subDays(2) ]);
        factory('App\UserMetadata')->create(['user_id' => $artist1->id, 'pin_count' => 10, 'like_count' => 10, 'comment_count' => 10 ]);
        $artist2 = factory('App\User')->create([ 'user_type_id' => 6, 'profile_picture' => 'img/needle-image2.jpg', 'created_at' => Carbon::now()->subDays(2) ]);
        factory('App\UserMetadata')->create(['user_id' => $artist2->id, 'pin_count' => 20, 'like_count' => 10, 'comment_count' => 10 ]);
        $artist3 = factory('App\User')->create([ 'user_type_id' => 6, 'profile_picture' => 'img/needle-image3.jpg', 'created_at' => Carbon::now()->subDays(2) ]);
        factory('App\UserMetadata')->create(['user_id' => $artist3->id, 'pin_count' => 30, 'like_count' => 10, 'comment_count' => 10 ]);
        $artist4 = factory('App\User')->create([ 'user_type_id' => 6, 'profile_picture' => 'img/needle-image4.jpg', 'created_at' => Carbon::now()->subDays(2) ]);
        factory('App\UserMetadata')->create(['user_id' => $artist4->id, 'pin_count' => 40, 'like_count' => 10, 'comment_count' => 10 ]);
    
        $other_artists = factory('App\User', 5)->create([ 'user_type_id' => 6]);
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
        $gallery1 = factory('App\User')->create([ 'user_type_id' => 4, 'profile_picture' => 'img/needle-image1.jpg']);
        factory('App\UserMetadata')->create(['user_id' => $gallery1->id, 'pin_count' => 10, 'like_count' => 10, 'comment_count' => 10 ]);
        $gallery2 = factory('App\User')->create([ 'user_type_id' => 4, 'profile_picture' => 'img/needle-image1.jpg']);
        factory('App\UserMetadata')->create(['user_id' => $gallery2->id, 'pin_count' => 20, 'like_count' => 10, 'comment_count' => 10 ]);
        $gallery3 = factory('App\User')->create([ 'user_type_id' => 4, 'profile_picture' => 'img/needle-image1.jpg']);
        factory('App\UserMetadata')->create(['user_id' => $gallery3->id, 'pin_count' => 30, 'like_count' => 10, 'comment_count' => 10 ]);
        $gallery4 = factory('App\User')->create([ 'user_type_id' => 4, 'profile_picture' => 'img/needle-image1.jpg']);
        factory('App\UserMetadata')->create(['user_id' => $gallery4->id, 'pin_count' => 40, 'like_count' => 10, 'comment_count' => 10 ]);
    
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
        $collector1 = factory('App\User')->create([ 'user_type_id' => 3, 'profile_picture' => 'img/needle-image1.jpg']);
        factory('App\UserMetadata')->create(['user_id' => $collector1->id, 'pin_count' => 10, 'like_count' => 10, 'comment_count' => 10 ]);
        $collector2 = factory('App\User')->create([ 'user_type_id' => 3, 'profile_picture' => 'img/needle-image1.jpg']);
        factory('App\UserMetadata')->create(['user_id' => $collector2->id, 'pin_count' => 20, 'like_count' => 10, 'comment_count' => 10 ]);
        $collector3 = factory('App\User')->create([ 'user_type_id' => 3, 'profile_picture' => 'img/needle-image1.jpg']);
        factory('App\UserMetadata')->create(['user_id' => $collector3->id, 'pin_count' => 30, 'like_count' => 10, 'comment_count' => 10 ]);
        $collector4 = factory('App\User')->create([ 'user_type_id' => 3, 'profile_picture' => 'img/needle-image1.jpg']);
        factory('App\UserMetadata')->create(['user_id' => $collector4->id, 'pin_count' => 40, 'like_count' => 10, 'comment_count' => 10 ]);
    
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
        $enthusiast1 = factory('App\User')->create([ 'user_type_id' => 5, 'profile_picture' => 'img/needle-image1.jpg']);
        factory('App\UserMetadata')->create(['user_id' => $enthusiast1->id, 'pin_count' => 10, 'like_count' => 10, 'comment_count' => 10 ]);
        $enthusiast2 = factory('App\User')->create([ 'user_type_id' => 5, 'profile_picture' => 'img/needle-image1.jpg']);
        factory('App\UserMetadata')->create(['user_id' => $enthusiast2->id, 'pin_count' => 20, 'like_count' => 10, 'comment_count' => 10 ]);
        $enthusiast3 = factory('App\User')->create([ 'user_type_id' => 5, 'profile_picture' => 'img/needle-image1.jpg']);
        factory('App\UserMetadata')->create(['user_id' => $enthusiast3->id, 'pin_count' => 30, 'like_count' => 10, 'comment_count' => 10 ]);
        $enthusiast4 = factory('App\User')->create([ 'user_type_id' => 5, 'profile_picture' => 'img/needle-image1.jpg']);
        factory('App\UserMetadata')->create(['user_id' => $enthusiast4->id, 'pin_count' => 40, 'like_count' => 10, 'comment_count' => 10 ]);
    
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
        $professional1 = factory('App\User')->create([ 'user_type_id' => 7, 'profile_picture' => 'img/needle-image1.jpg']);
        factory('App\UserMetadata')->create(['user_id' => $professional1->id, 'pin_count' => 10, 'like_count' => 10, 'comment_count' => 10 ]);
        $fair = factory('App\User')->create([ 'user_type_id' => 8, 'profile_picture' => 'img/needle-image1.jpg']);
        factory('App\UserMetadata')->create(['user_id' => $fair->id, 'pin_count' => 20, 'like_count' => 10, 'comment_count' => 10 ]);
        $institute3 = factory('App\User')->create([ 'user_type_id' => 9, 'profile_picture' => 'img/needle-image1.jpg']);
        factory('App\UserMetadata')->create(['user_id' => $institute3->id, 'pin_count' => 30, 'like_count' => 10, 'comment_count' => 10 ]);
        $institute4 = factory('App\User')->create([ 'user_type_id' => 9, 'profile_picture' => 'img/needle-image1.jpg']);
        factory('App\UserMetadata')->create(['user_id' => $institute4->id, 'pin_count' => 50, 'like_count' => 10, 'comment_count' => 10 ]);
    
        //act
        $response = $this->get('/api/user-types/avatars');

        //assert
        $response->assertJsonFragment([
            "professional" => [ $institute4->profile_picture, $institute4->profile_picture, $fair->profile_picture, $professional1->profile_picture ]
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

    /**
     * @test
     * a logged in user can change his password
     */
    public function a_logged_in_user_can_change_his_password()
    {
        //arrange
        $this->signIn();

        //act
        $response = $this->patch("api/password", [
                'old_password' => 'secret',
                'new_password' => 'newpassword'
            ]);

        //assert
        if (Hash::check('newpassword', $this->user->password))
        {
            $this->assertEquals(1, 1);
        }else{
            $this->assertEquals(1, 0);
        }
    }

    /**
     * @test
     * confirm that test db is in use
     */
    public function confirm_that_test_db_is_in_use()
    {
        //act
        $response = $this->get('/api/feed');

        //assert
        $this->assertCount(0, $response->json()['data']);
    }
}

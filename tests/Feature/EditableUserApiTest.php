<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class EditableUserApiTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @test
     * only a logged in user can edit his/her information
     */
    public function only_a_logged_in_user_can_edit_his_her_information()
    {
    	//arrange
        $response = $this->json('PUT','api/user');
        $response->assertStatus(401);
        
        $this->signIn();
        $response = $this->json('PUT','api/user');
        $this->assertNotEquals(401, $response->getStatusCode());        
    }

    /**
     * @test
     * a user can update 8 core fields in one go
     */
    public function a_user_can_update_8_core_fields_in_one_go()
    {
        //arrange
        $this->signIn();
        $user_type = factory('App\UserType')->create(['id' => 5]);
        
        $new_data = [
            'id' => $this->user->id,
            'name' => 'New Name',
            'email' => 'jsddf@gmail.com',
            "sex" => 1,
            'user_type_id' => $user_type->id,
            'website' => 'http://www.google.com',
            'biography' => 'test biography',
            'phone' => '01778999944',
            'gcm_registration_key' => 'dummy_key',
            'username' => 'dummyusername'
        ];

        //act
        $response = $this->json('PUT','api/user',$new_data);

        //assert
        $this->assertDatabaseHas('users', $new_data);
    }

    /**
     * @test
     * users can also update specific fields
     */
    public function users_can_also_update_specific_fields()
    {
        //arrange
        $this->signIn();
    
        $new_data = [
            'id' => $this->user->id,
            'name' => 'New Name',
            "sex" => 2
        ];

        //act
        $response = $this->json('PUT','api/user',$new_data);

        //assert
        $this->assertDatabaseHas('users', $new_data);
    }

    /**
     * @test
     * user can not update their username if it is already taken
     */
    public function user_can_not_update_their_username_if_it_is_already_taken()
    {
        //arrange
        $this->signIn();
        $another_user = factory('App\User')->create(['username'=>'newusername']);
    
        $new_data = [
            'id' => $this->user->id,
            'username' => 'newusername',
            'name' => 'New Name'
        ];
    
        //act
        $response = $this->json('PUT','api/user',$new_data);

        //assert
        $response->assertStatus(422);
    }

    /**
     * @test
     * user can update their username if it is available
     */
    public function user_can_update_their_username_if_it_is_available()
    {
        //arrange
        $this->signIn();
        $new_data = [
            'id' => $this->user->id,
            'username' => 'newusername',
            'name' => 'New Name'
        ];
    
        //act
        $response = $this->json('PUT','api/user',$new_data);
    
        //assert
        $this->assertDatabaseHas('users', $new_data);
    }

    /**
     * @test
     * user can not update their email address if it is already taken
     */
    public function user_can_not_update_their_email_address_if_it_is_already_taken()
    {
        //arrange
        $this->signIn();
        $another_user = factory('App\User')->create(['email'=>'email@gmail.com']);
    
        $new_data = [
            'id' => $this->user->id,
            'email' => 'email@gmail.com',
            'name' => 'New Name'
        ];
    
        //act
        $response = $this->json('PUT','api/user',$new_data);

        //assert
        $response->assertStatus(422);
    }

    /**
     * @test
     * user can update their email address if it is available
     */
    public function user_can_update_their_email_address_if_it_is_available()
    {
        //arrange
        $this->signIn();
        $new_data = [
            'id' => $this->user->id,
            'email' => $this->user->email,
            'name' => 'New Name'
        ];
    
        //act
        $response = $this->json('PUT','api/user',$new_data);
    
        //assert
        $this->assertDatabaseHas('users', $new_data);
    }
}

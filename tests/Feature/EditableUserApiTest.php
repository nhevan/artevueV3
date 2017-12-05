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
     * a logged in user can update their profile picture
     */
    public function a_logged_in_user_can_update_their_profile_picture()
    {
        //arrange
        $this->signIn();
    
        //act
        $response = $this->post('/api/update-profile-picture', ['profile_picture' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAABREAAAJPCAYAAADrIZMWAAABfGlDQ1BJQ0MgUHJvZmlsZQAAKJFjYGAqSSwoyGFhYGDIzSspCnJ3UoiIjFJgv8PAzcDDIMRgxSCemFxc4BgQ4MOAE3y7xsAIoi/rgsxK8/x506a1fP4WNq+ZclYlOrj1gQF3SmpxMgMDIweQnZxSnJwLZOcA2TrJBUUlQPYMIFu3vKQAxD4BZIsUAR0IZN8BsdMh7A8gdhKYzcQCVhMS5AxkSwDZAkkQtgaInQ5hW4DYyRmJKUC2B8guiBvAgNPDRcHcwFLXkYC7SQa5OaUwO0ChxZOaFxoMcgcQyzB4MLgwKDCYMxgwWDLoMjiWpFaUgBQ65xdUFmWmZ5QoOAJDNlXBOT+3oLQktUhHwTMvWU9HwcjA0ACkDhRnEKM/B4FNZxQ7jxDLX8jAYKnMwMDcgxBLmsbAsH0PA4PEKYSYyjwGBn5rBoZt5woSixLhDmf8xkKIX5xmbARh8zgxMLDe+///sxoDA/skBoa/E////73'], [ 'X-ARTEVUE-App-Version' => '2.0' ]);
    
        //assert
        $response->assertStatus(200);
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

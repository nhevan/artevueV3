<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use App\Mail\NewPasswordEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class AdminUserRelatedTest extends DuskTestCase
{
    use DatabaseMigrations;
    
    protected $admin;

    public function setUp()
    {
        parent::setUp();

        $admin_type = factory('App\UserType')->create(['id' => 2]);
        $this->admin = factory('App\User')->create(['user_type_id' => 2]);
    }

    /**
     * @test
     * admins can search users 
     */
    public function admins_can_search_users()
    {
        $ben = factory('App\User')->create(['username' => 'bentumia']);
        $someone = factory('App\User')->create(['username' => 'someone']);

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                    ->visit('/home')
                    ->type('search_string', 'ben')
                    ->press('Search User')
                    ->assertSee('ben')
                    ->assertDontSee('someone');
        });
    }

    /**
     * @test
     * admins can send password reset email to any user
     */
    public function admins_can_send_password_reset_email_to_any_user()
    {
        //arrange
        $usermeta = factory('App\UserMetadata')->create();
        $user = $usermeta->user;
    
        //act
        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($this->admin)
                    ->visit('/users')
                    ->click('#user-detail-'.$user->id)
                    ->clickLink('Email new password')
                    ->assertSee('Password reset email successfully sent !');
        });
    
    }

    /**
     * @test
     * admins can directly change the password of any user
     */
    public function admins_can_directly_change_the_password_of_any_user()
    {
        //arrange
        $usermeta = factory('App\UserMetadata')->create();
        $user = $usermeta->user;
    
        //act
        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($this->admin)
                    ->visit('/users')
                    ->click('#user-detail-'.$user->id)
                    ->click("#change-password")
                    ->assertSee('Enter new passoword')
                    ->type('new_password', '123456')
                    ->assertSee('Confirm password')
                    ->type('confirm_password', '123456')
                    ->press('Change Password')
                    ->assertSee('Password successfully changed !')
                    ->assertRouteIs('users.show', ['user' => $user->id]);
        });
    }

    /**
     * @test
     * admins can change the username of any user
     */
    public function admins_can_change_the_username_of_any_user()
    {
        //arrange
        $usermeta = factory('App\UserMetadata')->create();
        $user = $usermeta->user;
    
        //act
        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($this->admin)
                    ->visit('/users')
                    ->click('#user-detail-'.$user->id)
                    ->click("#change-username")
                    ->type('username', '123456')
                    ->press('Change username')
                    ->assertSee('Username successfully changed !')
                    ->assertRouteIs('users.show', ['user' => $user->id]);
        });
    }
}

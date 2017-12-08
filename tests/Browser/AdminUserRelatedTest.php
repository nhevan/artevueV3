<?php

namespace Tests\Browser;

use Carbon\Carbon;
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
        $this->seed('PostArtTypesTableSeeder');
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
                    ->visit('/dashboard')
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
        $this->seed('EmailTemplatesSeeder');
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
                    ->type('new_password', '1234567')
                    ->assertSee('Confirm password')
                    ->type('confirm_password', '1234567')
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

    /**
     * @test
     * admins can sort users by name
     */
    public function admins_can_sort_users_by_name()
    {
        //arrange
        factory('App\User', 35)->create();
        $alphabetA = factory('App\User')->create(['name' => 'AAAAAA']);
        $alphabetZ = factory('App\User')->create(['name' => 'ZZZZZZ']);
        factory('App\User', 35)->create();
    
        //act
        $this->browse(function (Browser $browser) use ($alphabetA, $alphabetZ) {
            $browser->loginAs($this->admin)
                    ->visit('/users')
                    ->assertDontSee($alphabetA->name)
                    ->clickLink('Name')
                    ->assertSee($alphabetA->name)
                    ->assertDontSee($alphabetZ->name)
                    ->clickLink('Name')
                    ->assertSee($alphabetZ->name)
                    ->assertDontSee($alphabetA->name);
        });
    }

    /**
     * @test
     * admins can sort users by join date
     */
    public function admins_can_sort_users_by_join_date()
    {
        //arrange
        factory('App\User', 35)->create(['created_at' => Carbon::now()->subHours(2)]);
        $recent_post = factory('App\User')->create();
        $old_post = factory('App\User')->create(['created_at' => Carbon::now()->subHours(8)]);
        factory('App\User', 35)->create(['created_at' => Carbon::now()->subHours(2)]);
        
        //act
        $this->browse(function (Browser $browser) use ($old_post, $recent_post) {
            $browser->loginAs($this->admin)
                    ->visit('/users')
                    ->assertDontSee($old_post->name)
                    ->clickLink('Join Date')
                    ->assertSee($old_post->name)
                    ->assertDontSee($recent_post->name)
                    ->clickLink('Join Date')
                    ->assertSee($recent_post->name)
                    ->assertDontSee($old_post->name);
        });
    }

    /**
     * @test
     * admins can send notification to individual users
     */
    public function admins_can_send_notification_to_individual_users()
    {
        //arrange
        $usermeta = factory('App\UserMetadata')->create();
        $user = $usermeta->user;
    
        //act
        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($this->admin)
                    ->visit('/users')
                    ->click('#user-detail-'.$user->id)
                    ->clickLink('Send notification')
                    ->assertSee('Send personal notification to '.$user->username)
                    ->type('notification', 'test notification')
                    ->press('Send notification')
                    ->assertSee('Notification successfully sent!')
                    ->assertRouteIs('users.show', ['user' => $user->id]);
        });
    }

    /**
     * @test
     * admins can send notification to all users at once
     */
    public function admins_can_send_notification_to_all_users_at_once()
    {
        //act
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                    ->visit('/send-notification-form')
                    ->assertSee('Send personal notification to all users')
                    ->type('notification', 'test notification')
                    ->press('Send notification')
                    ->assertSee('Notification successfully sent!')
                    ->assertRouteIs('users.index');
        });
    }
}

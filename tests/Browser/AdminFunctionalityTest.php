<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use App\Mail\NewPasswordEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class AdminFunctionalityTest extends DuskTestCase
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
     * admins can delete any post
     */
    public function admins_can_delete_any_post()
    {
        $usermeta = factory('App\UserMetadata')->create();
        $post = factory('App\Post')->create(['owner_id' => $usermeta->user_id]);
        $other = factory('App\Post')->create();

        $this->browse(function (Browser $browser) use ($post, $other) {
            $browser->loginAs($this->admin)
                    ->visit('/posts')
                    ->assertSee($post->owner->name)
                    ->assertSee($other->owner->name)
                    ->click('#delete-post-'.$post->id)
                    ->acceptDialog()
                    ->assertRouteIs('posts.index')
                    ->assertSee($other->owner->name)
                    ->assertDontSee($post->owner->name);
        });
    }

    /**
     * @test
     * after deleting a specific users posts admins are redirected to the users posts page
     */
    public function after_deleting_a_specific_users_posts_admins_are_redirected_to_the_users_posts_page()
    {
        $usermeta = factory('App\UserMetadata')->create();
        $post = factory('App\Post')->create(['owner_id' => $usermeta->user_id]);

        $this->browse(function (Browser $browser) use ($post, $usermeta) {
            $browser->loginAs($this->admin)
                    ->visit('/users')
                    ->click('#user-detail-'.$usermeta->user_id)
                    ->clickLink('View Posts')
                    ->assertRouteIs('users.posts', ['user_id' => $usermeta->user_id])
                    ->assertSee($post->owner->name)
                    ->click('#delete-post-'.$post->id)
                    ->acceptDialog()
                    ->assertRouteIs('users.posts', ['user_id' => $usermeta->user_id])
                    ->assertDontSee($post->owner->name);
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
     * admins can edit a posts description price address_title and address
     */
    public function admins_can_edit_a_posts_description_price_address_title_and_address()
    {
        //arrange
        $usermeta = factory('App\UserMetadata')->create();
        $user = $usermeta->user;
        $post = factory('App\Post')->create([
            'owner_id' => $user->id,
            'description' => 'old description',
            'price' => '10.99',
            'address_title' => 'old address title',
            'address' => 'old address'
        ]);
    
        //act
        $this->browse(function (Browser $browser) use ($post) {
            $browser->loginAs($this->admin)
                    ->visit('/posts')
                    ->click('#post-detail-'.$post->id)
                    ->click("#edit-post")
                    ->assertInputValue('description', $post->description)
                    ->type('description', 'new description')
                    ->assertInputValue('price', $post->price)
                    ->type('price', '99.99')
                    ->assertInputValue('address_title', $post->address_title)
                    ->type('address_title', 'new address_title')
                    ->assertInputValue('address', $post->address)
                    ->type('address', 'new address')
                    ->press('Submit Changes')
                    ->assertRouteIs('posts.show', ['post' => $post->id])
                    ->assertSee('new description')
                    ->assertSee('99.99')
                    ->assertSee('new address_title')
                    ->assertSee('new address');
        });

        $this->assertDatabaseHas('posts', [
                'id' => $post->id,
                'price' => '99.99'
            ]);
    }

    /**
     * @test
     * admins can view all settings by visiting the settings url
     */
    public function admins_can_view_all_settings_by_visiting_the_settings_url()
    {
        //arrange
        $this->seed('SettingsTableSeeder');
    
        //act
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                    ->visit('/settings')
                    ->assertSee('ios_latest_app_version')
                    ->assertSee('ios_min_app_version')
                    ->assertSee('chronological_weight_distribution')
                    ->assertSee('like_weight_distribution')
                    ->assertDontsee('something random');
        });
    }

    /**
     * @test
     * admins can update app version settings
     */
    public function admins_can_update_app_version_settings()
    {
        //arrange
        $this->seed('SettingsTableSeeder');
    
        //act
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->admin)
                    ->visit('/settings')
                    ->click('#edit-app-settings')
                    ->type('ios_latest_app_version-value', '1.55')
                    ->type('ios_latest_app_version-description', 'new description')
                    ->press('Update Settings')
                    ->assertRouteIs('settings.index')
                    ->assertSee('1.55')
                    ->assertSee('new description');
        });
    }
}

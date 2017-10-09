<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
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
}

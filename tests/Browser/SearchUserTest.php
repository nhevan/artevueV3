<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class SearchUserTest extends DuskTestCase
{
    use DatabaseMigrations;
    /**
     * @test
     * admins can search users 
     */
    public function admins_can_search_users ()
    {
        $admin_type = factory('App\UserType')->create(['id' => 2]);
        $admin = factory('App\User')->create(['user_type_id' => 2]);
        $ben = factory('App\User')->create(['username' => 'bentumia']);
        $someone = factory('App\User')->create(['username' => 'someone']);

        $this->browse(function (Browser $browser) use ($admin) {
            $browser->loginAs($admin)
                    ->visit('/home')
                    ->type('search_string', 'ben')
                    ->press('Search User')
                    ->assertSee('ben')
                    ->assertDontSee('someone');
        });
    }
}

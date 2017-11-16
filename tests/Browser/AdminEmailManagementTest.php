<?php

namespace Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use App\Mail\WelcomeEmail;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class AdminEmailManagementTest extends DuskTestCase
{
    use DatabaseMigrations;

    protected $admin;

    public function setUp()
    {
        parent::setUp();
        
        $this->seed('EmailTemplatesSeeder');

        $admin_type = factory('App\UserType')->create(['id' => 2]);
        $this->admin = factory('App\User')->create(['user_type_id' => 2]);
    }

    /**
     * @test
     * admins can see a menu item to access email templates
     */
    public function admins_can_see_a_menu_item_to_access_email_templates()
    {
        $this->browse(function (Browser $browser){
            $browser->loginAs($this->admin)
                    ->visit('/home')
                    ->assertSee('Email Templates');
        });
    }

    /**
     * @test
     * admin sees list of 4 default email templates when email template page is visited
     */
    public function admin_sees_list_of_4_default_email_templates_when_email_template_page_is_visited()
    {
        $this->browse(function (Browser $browser){
            $browser->loginAs($this->admin)
                    ->visit('/mails/templates')
                    ->assertSee('Welcome Email');
        });
    }

    /**
     * @test
     * admins can visit preview links to preview email templates in the browser
     */
    public function admins_can_visit_preview_links_to_preview_email_templates_in_the_browser()
    {
        factory('App\Post')->create(['owner_id' => $this->admin->id]);
        factory('App\User')->create(['username' => 'nhevan']);

        $this->browse(function (Browser $browser){
            $browser->loginAs($this->admin)
                    ->visit('/mails/1/preview')
                    ->assertSee('Dear '.$this->admin->name)
                    ->visit('/mails/2/preview')
                    ->assertSee('Dear '.$this->admin->name)
                    ->visit('/mails/3/preview')
                    ->assertSee('Dear '.$this->admin->name)
                    ->visit('/mails/4/preview')
                    ->assertSee('Dear '.$this->admin->name);
        });
    }

    /**
     * @test
     * admins can send test welcome email
     */
    public function admins_can_send_test_welcome_email()
    {
        $this->browse(function (Browser $browser){
            
            $browser->loginAs($this->admin)
                    ->visit('/mails/1/test')
                    ->assertSee('Test email sent to your email address !');
            
        });
    }

    /**
     * @test
     * admins can send test password reset email
     */
    public function admins_can_send_test_password_reset_email()
    {
        $this->browse(function (Browser $browser){
            
            $browser->loginAs($this->admin)
                    ->visit('/mails/2/test')
                    ->assertSee('Test email sent to your email address !');
            
        });
    }

    /**
     * @test
     * admins can send test announcement email
     */
    public function admins_can_send_test_announcement_email()
    {
        $this->browse(function (Browser $browser){
            
            $browser->loginAs($this->admin)
                    ->visit('/mails/3/test')
                    ->assertSee('Test email sent to your email address !');
            
        });
    }

    /**
     * @test
     * admins can send test buy post request email
     */
    public function admins_can_send_test_buy_post_request_email()
    {
        factory('App\Post')->create(['owner_id' => $this->admin->id]);
        factory('App\User')->create(['username' => 'nhevan']);

        $this->browse(function (Browser $browser){
            $browser->loginAs($this->admin)
                    ->visit('/mails/4/test')
                    ->assertSee('Test email sent to your email address !');
        });
    }

    /**
     * @test
     * admins can edit an email template by visiting the edit template page
     */
    public function admins_can_edit_an_email_template_by_visiting_the_edit_template_page()
    {
        $this->browse(function (Browser $browser){
            $browser->loginAs($this->admin)
                    ->visit('/mails/1/edit')
                    ->type('sender_email', 'nhevan@gmail.com')
                    ->type('sender_name', 'NH Evan')
                    ->type('subject', 'Welcome test subject')
                    ->press('Save')
                    ->acceptDialog()
                    ->assertSee('Email template successfully updated!');
        });
    }

    /**
     * @test
     * admins can dispatch announcement email to all Artevue users
     */
    public function admins_can_dispatch_announcement_email_to_all_Artevue_users()
    {
        $this->browse(function (Browser $browser){
            $browser->loginAs($this->admin)
                    ->visit('/mails/dispatch-announcement')
                    ->assertSee('Announcement emails are now being sent to all Artevue users.');
        });
    }
}

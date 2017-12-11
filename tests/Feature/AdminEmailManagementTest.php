<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Mail\AnnouncementEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AdminEmailManagementTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @test
     * admins can dispatch announcement emails to all users
     */
    public function admins_can_dispatch_announcement_emails_to_all_users()
    {
    	//arrange
        Mail::fake();
        $this->seed('EmailTemplatesSeeder');
        $this->seed('UserTypesTableSeeder');
        $admin = factory('App\User')->create(['user_type_id' => 2]);
        $this->signIn($admin);
        $user2 = factory('App\User')->create();
    
        //act
    	$response = $this->get("/mails/dispatch-announcement");
    	$new_user = factory('App\User')->create();

        //assert
        Mail::assertSent(AnnouncementEmail::class, function($mail){
        	return $mail->user->id === $this->user->id;
        });
        Mail::assertSent(AnnouncementEmail::class, function($mail) use ($user2){
        	return $mail->user->id === $user2->id;
        });
        Mail::assertNotSent(AnnouncementEmail::class, function($mail) use ($new_user){
        	return $mail->user->id === $new_user->id;
        });
    }
}

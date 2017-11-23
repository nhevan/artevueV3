<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class LogMixpanelActionsTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @test
     * a job is dispatched when a authenticated user requests the feed view
     */
    public function a_job_is_dispatched_when_a_authenticated_user_requests_the_feed_view()
    {
    	//arrange
        $this->signIn();
    
        //act
        $this->expectsJobs(\App\Jobs\SendMixpanelAction::class);
    	$this->get('/api/feed');
    }

    /**
     * @test
     * when a mixpanel job is dispatched the mixpanel actions table stores the action information
     */
    public function when_a_mixpanel_job_is_dispatched_the_mixpanel_actions_table_stores_the_action_information()
    {
    	//arrange
        $this->signIn();
    
        //act
    	$response = $this->get('/api/feed');

        //assert
        $this->assertDatabaseHas('mixpanel_actions', [
        		'user_id' => $this->user->id,
        		'action' => 'Feed View'
        	]);
    }
}

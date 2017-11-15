<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class SocialMediaFunctionalitiesTest extends TestCase
{
    use DatabaseTransactions;
    /**
     * @test
     * users can search their common fb friends that are using our system
     */
    public function users_can_search_their_common_fb_friends_that_are_using_our_system()
    {
        //arrange
        $user = factory('App\User')->create([
        	'social_media' => 'facebook',
        	'social_media_uid' => '1555685954491783',
        	'social_media_access_token' => 'EAAEKXpBRuKwBALEd9JKhmTCELHqKsSCj0YxGgkyQlkZB9R20SRZAwovgQvkn4dZCogaXoTSPziQhcOwVc7OZA9DfEh445wqC7iVGVYoJLFEN9WZBPZAaQA24Xv4ZCSiZCeusejt4JMCSzT50rAlY1wTDxQ12JLHDlj0CH5mUmmdbshydsiUwXHbi6576QK4CqPYQcZCsZATrb8LwZDZD'
    	]);

    	$users_friend = factory('App\User')->create([
    		'social_media' => 'facebook',
        	'social_media_uid' => '10214331734277272',
        	'social_media_access_token' => 'whatever'
		]);

		$this->signIn($user);
    	
        //act
        $response = $this->json( 'GET', "/api/find-friends");
        // dd($response->json());
        //assert
    	$response->assertStatus(200);
    	$this->assertEquals(1, sizeof($response->json()['data']));
    }

    /**
     * @test
     * users can look for their instagram followed users in Artevue
     */
    public function users_can_look_for_their_instagram_followed_users_in_Artevue()
    {
        //arrange
        $user = factory('App\User')->create([
            'social_media' => 'instagram',
            'social_media_uid' => '906884564',
            'social_media_access_token' => '906884564.5035be1.ac0b108ed36b4e5f9a906ff8fa217f6f'
        ]);

        $users_friend = factory('App\User')->create([
            'social_media' => 'instagram',
            'social_media_uid' => '266851173',
            'social_media_access_token' => 'whatever-their-access-token-is'
        ]);

        $other_users = factory('App\User')->create([
            'social_media' => 'instagram',
            'social_media_uid' => '267245731231',
            'social_media_access_token' => 'whatever-their-access-token-is'
        ]);

        $this->signIn($user);
    
        //act
        $response = $this->json( 'GET', "/api/find-friends");
    
        //assert
        $response->assertStatus(200);
        $this->assertEquals(1, sizeof($response->json()['data'])); 
    }

    /**
     * @test
     * users also get to see the list of users from instagram that are following him/her
     */
    public function users_also_get_to_see_the_list_of_users_from_instagram_that_are_following()
    {
        //arrange
        $user = factory('App\User')->create([
            'social_media' => 'instagram',
            'social_media_uid' => '906884564',
            'social_media_access_token' => '906884564.5035be1.ac0b108ed36b4e5f9a906ff8fa217f6f'
        ]);

        $users_friend = factory('App\User')->create([
            'social_media' => 'instagram',
            'social_media_uid' => '435981327',
            'social_media_access_token' => 'whatever-their-access-token-is'
        ]);

        $this->signIn($user);
    
        //act
        $response = $this->json( 'GET', "/api/find-friends");
    
        //assert
        $response->assertStatus(200);
        $this->assertEquals(1, sizeof($response->json()['data'])); 
    }
}

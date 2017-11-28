<?php

namespace Tests\Feature;

use App\Post;
use App\Hashtag;
use Tests\TestCase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class HashtagTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @test
     * when a user creates a post with a hashtag the hasgtag is explicitly stored in the db
     */
    public function when_a_user_creates_a_post_with_a_hashtag_the_hasgtag_is_explicitly_stored_in_the_db()
    {
    	//arrange
        Storage::fake('s3');
        $this->signIn();
        $post = factory('App\Post')->make( [
        	'post_image' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAABREAAAJPCAYAAADrIZMWAAABfGlDQ1BJQ0MgUHJvZmlsZQAAKJFjYGAqSSwoyGFhYGDIzSspCnJ3UoiIjFJgv8PAzcDDIMRgxSCemFxc4BgQ4MOAE3y7xsAIoi/rgsxK8/x506a1fP4WNq+ZclYlOrj1gQF3SmpxMgMDIweQnZxSnJwLZOcA2TrJBUUlQPYMIFu3vKQAxD4BZIsUAR0IZN8BsdMh7A8gdhKYzcQCVhMS5AxkSwDZAkkQtgaInQ5hW4DYyRmJKUC2B8guiBvAgNPDRcHcwFLXkYC7SQa5OaUwO0ChxZOaFxoMcgcQyzB4MLgwKDCYMxgwWDLoMjiWpFaUgBQ65xdUFmWmZ5QoOAJDNlXBOT+3oLQktUhHwTMvWU9HwcjA0ACkDhRnEKM/B4FNZxQ7jxDLX8jAYKnMwMDcgxBLmsbAsH0PA4PEKYSYyjwGBn5rBoZt5woSixLhDmf8xkKIX5xmbARh8zgxMLDe+///sxoDA/skBoa/E////73',
        	'description' => 'test description with a #test #secondtest hashtag'
    	] );

        //act
        $response = $this->post('api/post', $post->toArray() , [ 'X-ARTEVUE-App-Version' => '2.0' ]);

        //assert
        $hashtag1 = Hashtag::where('hashtag', '#test')->first();
        $hashtag2 = Hashtag::where('hashtag', '#secondtest')->first();
        $post_id = Post::where('owner_id', $this->user->id)->first()->id;
        
        $response->assertSuccessful();
        $this->assertDatabaseHas('hashtags', ['hashtag' => $hashtag1->hashtag]);
        $this->assertDatabaseHas('hashtags', ['hashtag' => $hashtag2->hashtag]);
        $this->assertDatabaseHas('hashtag_post', [
        	'hashtag_id' => $hashtag1->id,
			'post_id' => $post_id
    	]);
    	$this->assertDatabaseHas('hashtag_post', [
        	'hashtag_id' => $hashtag2->id,
			'post_id' => $post_id
    	]);
    }

    /**
     * @test
     * a user can fetch all top posts marked with a given hashtag
     */
    public function a_user_can_fetch_all_top_posts_marked_with_a_given_hashtag()
    {
    	//arrange
        Storage::fake('s3');
        $this->signIn();
        $post = factory('App\Post')->make( [
        	'post_image' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAABREAAAJPCAYAAADrIZMWAAABfGlDQ1BJQ0MgUHJvZmlsZQAAKJFjYGAqSSwoyGFhYGDIzSspCnJ3UoiIjFJgv8PAzcDDIMRgxSCemFxc4BgQ4MOAE3y7xsAIoi/rgsxK8/x506a1fP4WNq+ZclYlOrj1gQF3SmpxMgMDIweQnZxSnJwLZOcA2TrJBUUlQPYMIFu3vKQAxD4BZIsUAR0IZN8BsdMh7A8gdhKYzcQCVhMS5AxkSwDZAkkQtgaInQ5hW4DYyRmJKUC2B8guiBvAgNPDRcHcwFLXkYC7SQa5OaUwO0ChxZOaFxoMcgcQyzB4MLgwKDCYMxgwWDLoMjiWpFaUgBQ65xdUFmWmZ5QoOAJDNlXBOT+3oLQktUhHwTMvWU9HwcjA0ACkDhRnEKM/B4FNZxQ7jxDLX8jAYKnMwMDcgxBLmsbAsH0PA4PEKYSYyjwGBn5rBoZt5woSixLhDmf8xkKIX5xmbARh8zgxMLDe+///sxoDA/skBoa/E////73',
        	'description' => 'top posts for hashtag - with a #test #secondtest hashtag'
    	] );
    	$this->post('api/post', $post->toArray() , [ 'X-ARTEVUE-App-Version' => '2.0' ]);
    	$this->post('api/post', $post->toArray() , [ 'X-ARTEVUE-App-Version' => '2.0' ]);
    	$this->post('api/post', $post->toArray() , [ 'X-ARTEVUE-App-Version' => '2.0' ]);
    	$this->post('api/post', $post->toArray() , [ 'X-ARTEVUE-App-Version' => '2.0' ]);
    	$this->post('api/post', $post->toArray() , [ 'X-ARTEVUE-App-Version' => '2.0' ]);

        //act
    	$response = $this->get('api/hashtag/top-posts/test');

        //assert
		$response->assertJsonFragment([
			'description' => 'top posts for hashtag - with a #test #secondtest hashtag'
		]);
		$this->assertCount(5, $response->json()['data']);
    }

    /**
     * @test
     * a user can fetch the latest post for a given hashtag, latest posts excludes the top posts for that hashtag
     */
    public function a_user_can_fetch_the_latest_post_for_a_given_hashtag_latest_posts_excludes_the_top_posts_for_that_hashtag()
    {
    	//arrange
        Storage::fake('s3');
        $this->signIn();
        $hashtag = factory('App\Hashtag')->create(['hashtag' => '#test']);
        $posts = factory('App\Post', 9)->create( ['description' => "top posts for hashtag - with a #test #secondtest hashtag" ] );
        foreach ($posts->toArray() as $post) {
	        factory('App\PostHashtag')->create([
	        	'post_id' => $post['id'],
	        	'hashtag_id' => $hashtag->id
	    	]);
        }
        
        //act
        $latest_post = factory('App\Post')->make( [
        	'post_image' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAABREAAAJPCAYAAADrIZMWAAABfGlDQ1BJQ0MgUHJvZmlsZQAAKJFjYGAqSSwoyGFhYGDIzSspCnJ3UoiIjFJgv8PAzcDDIMRgxSCemFxc4BgQ4MOAE3y7xsAIoi/rgsxK8/x506a1fP4WNq+ZclYlOrj1gQF3SmpxMgMDIweQnZxSnJwLZOcA2TrJBUUlQPYMIFu3vKQAxD4BZIsUAR0IZN8BsdMh7A8gdhKYzcQCVhMS5AxkSwDZAkkQtgaInQ5hW4DYyRmJKUC2B8guiBvAgNPDRcHcwFLXkYC7SQa5OaUwO0ChxZOaFxoMcgcQyzB4MLgwKDCYMxgwWDLoMjiWpFaUgBQ65xdUFmWmZ5QoOAJDNlXBOT+3oLQktUhHwTMvWU9HwcjA0ACkDhRnEKM/B4FNZxQ7jxDLX8jAYKnMwMDcgxBLmsbAsH0PA4PEKYSYyjwGBn5rBoZt5woSixLhDmf8xkKIX5xmbARh8zgxMLDe+///sxoDA/skBoa/E////73',
        	'description' => 'latest post for hashtag - with a #test #secondtest hashtag'
    	] );

    	$this->post('api/post', $latest_post->toArray() , [ 'X-ARTEVUE-App-Version' => '2.0' ]);
    	$this->post('api/post', $latest_post->toArray() , [ 'X-ARTEVUE-App-Version' => '2.0' ]);

    	$response = $this->get('api/hashtag/latest-posts/test');
        //assert
		// $this->assertCount(2, $response->json()['data']);
		$response->assertJsonFragment([
			'description' => 'latest post for hashtag - with a #test #secondtest hashtag'
		]);
    }
}

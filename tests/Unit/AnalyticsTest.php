<?php

namespace Tests\Unit;

use App\User;
use App\Analytics;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AnalyticsTest extends TestCase
{
	use DatabaseTransactions;
	protected $analytics;

	public function setUp()
	{
		parent::setUp();
		$this->analytics = new Analytics;
	}

    /**
     * @test
     * it can set a model it is going to work on
     */
    public function it_can_set_a_model_it_is_going_to_work_on()
    {
    	//arrange and act
    	$this->analytics->filter('App\User');

    	//assert
        $this->assertEquals(new User, $this->analytics->getModel());   
    }

    /**
     * @test
     * it can return the count of a model
     */
    public function it_can_return_the_count_of_a_model()
    {
    	//arrange
    	factory(User::class, 5)->create();

        //act
        $count = $this->analytics->filter('App\User')->getCount();
    
        //assert
        $this->assertEquals(5, $count);
    }

    /**
     * @test
     * it can filter models with a given field and value
     */
    public function it_can_filter_models_with_a_given_field_and_value()
    {
    	//arrange
        factory(User::class, 5)->create();
        factory(User::class, 3)->create([ 'name' => 'filtered' ]);
    
        //act
    	$count = $this->analytics->filter('App\User')->where('name', 'filtered')->getCount();
    
        //assert
        $this->assertEquals(3, $count);
    }

    /**
     * @test
     * it can chain where methods for more fine tuned filtering
     */
    public function it_can_chain_where_methods_for_more_fine_tuned_filtering()
    {
    	//arrange
		factory(User::class, 5)->create();
        factory(User::class, 3)->create([ 'name' => 'filtered' ]);
        factory(User::class, 2)->create([ 'name' => 'filtered', 'website' => 'http://www.website.com' ]);

        //act
    	$count = $this->analytics->filter('App\User')->where('name', 'filtered')->where('website', 'http://www.website.com')->getCount();
    
        //assert
        $this->assertEquals(2, $count);
    }

    /**
     * @test
     * it can filter using a where not method
     */
    public function it_can_filter_using_a_where_not_null_method()
    {
        //arrange
        $artist = factory('App\Artist')->create();
        factory('App\Post', 4)->create(['artist_id' => $artist->id]);
        factory('App\Post', 2)->create(['artist_id' => null]);
    
        //act
        $count = $this->analytics->filter('App\Post')->whereNot('artist_id', null)->getCount();
    
        //assert
        $this->assertEquals(4, $count);
    }

    /**
     * @test
     * it can limit results by a given count
     */
    public function it_can_limit_results_by_a_given_count()
    {
        //arrange
        factory('App\User', 10)->create();
    
        //act
        $filtered_data = $this->analytics->filter('App\User')->limit(3)->get();

        //assert
        $this->assertCount(3, $filtered_data);
    }

    /**
     * @test
     * it can filter the top x models for the highest occurence count, for example top locations for posts
     */
    public function it_can_filter_the_top_x_models_for_the_highest_occurence_count_for_example_top_locations_for_posts()
    {
        //arrange
        factory('App\Post', 10)->create(['address_title' => 'London']);
        factory('App\Post', 8)->create(['address_title' => 'Paris']);
        factory('App\Post', 6)->create(['address_title' => 'Moscow']);
        factory('App\Post', 4)->create(['address_title' => 'New York']);
        factory('App\Post', 2)->create();
    
        //act
        $filtered_data = $this->analytics->filter('App\Post')->top('address_title')->limit(3)->get();

        //assert
        $this->assertEquals(['London', 'Paris', 'Moscow'], $filtered_data->pluck('address_title')->toArray());
        $this->assertCount(3, $filtered_data);
    }

    /**
     * @test
     * it can chain where and top methods
     */
    public function it_can_chain_where_and_top_methods()
    {
        //arrange
        factory('App\Post', 10)->create(['address_title' => 'London']);
        factory('App\Post', 8)->create(['address_title' => 'Paris']);
        factory('App\Post', 6)->create(['address_title' => 'Moscow']);
        factory('App\Post', 4)->create(['address_title' => 'New York']);
        factory('App\Post', 2)->create();
    
        //act
         $filtered_data = $this->analytics->filter('App\Post')->top('address_title')->whereNot('address_title', 'Moscow')->limit(3)->get();

        //assert
        $this->assertEquals(['London', 'Paris', 'New York'], $filtered_data->pluck('address_title')->toArray());
        $this->assertCount(3, $filtered_data);
    }
}

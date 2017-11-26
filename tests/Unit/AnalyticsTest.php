<?php

namespace Tests\Unit;

use App\User;
use App\Analytics;
use Carbon\Carbon;
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

    /**
     * @test
     * it can filter records by a given field name and a range of values for that field
     */
    public function it_can_filter_records_by_a_given_field_name_and_a_range_of_values_for_that_field()
    {
        //arrange
        factory('App\MixpanelActions', 5)->create([ 'created_at' => Carbon::now()->subDays(20) ]);
        factory('App\MixpanelActions', 5)->create([ 'created_at' => Carbon::now()->subDays(13) ]);
        factory('App\MixpanelActions', 5)->create([ 'created_at' => Carbon::now()->subDays(8) ]);
        factory('App\MixpanelActions', 5)->create([ 'created_at' => Carbon::now()->subDays(5) ]);

        //act
        $start_date = Carbon::now()->subDays(15);
        $end_date = Carbon::now()->subDays(6);
        $filtered_data = $this->analytics->filter('App\MixpanelActions')->whereBetween('created_at', $start_date, $end_date)->get();
    
        //assert
        $this->assertCount(10, $filtered_data);
    }

    /**
     * @test
     * it can return records in sets for given unit like day week month
     */
    public function it_can_return_records_in_sets_for_given_unit_like_day()
    {
        //arrange
        $start_date = Carbon::now()->subDays(10);
        $end_date = Carbon::now()->subDays(8);
        factory('App\MixpanelActions', 5)->create([ 'created_at' => Carbon::now()->subDays(20) ]);
        factory('App\MixpanelActions', 3)->create([ 'created_at' => $start_date ]);
        // dd(factory('App\MixpanelActions', 3)->create([ 'created_at' => Carbon::now()->subDays(10) ])->toArray());
        factory('App\MixpanelActions', 5)->create([ 'created_at' => $end_date ]);
        factory('App\MixpanelActions', 5)->create([ 'created_at' => Carbon::now()->subDays(5) ]);
    
        //act
        $filtered_data = $this->analytics->filter('App\MixpanelActions')->setXAxis($start_date, $end_date, 'day')->getByUnit();

        //assert
        $this->assertEquals([3, 0, 5], $filtered_data);
    }

    /**
     * @test
     * it can return records in sets for given unit like hour
     */
    public function it_can_return_records_in_sets_for_given_unit_like_hour()
    {
        //arrange
        factory('App\MixpanelActions', 5)->create([ 'created_at' => Carbon::now()->subHours(20) ]);
        factory('App\MixpanelActions', 3)->create([ 'created_at' => Carbon::now()->subHours(10) ]);
        factory('App\MixpanelActions', 5)->create([ 'created_at' => Carbon::now()->subHours(8) ]);
        factory('App\MixpanelActions', 5)->create([ 'created_at' => Carbon::now()->subHours(5) ]);
    
        //act
        $start_date = Carbon::now()->subHours(10);
        $end_date = Carbon::now()->subHours(8);
        $filtered_data = $this->analytics->filter('App\MixpanelActions')->setXAxis($start_date, $end_date, 'hour')->getByUnit();

        //assert
        $this->assertEquals([3, 0, 5], $filtered_data);
    }

    /**
     * @test
     * it can return records in sets for given unit like month
     */
    public function it_can_return_records_in_sets_for_given_unit_like_month()
    {
        //arrange
        factory('App\MixpanelActions', 5)->create([ 'created_at' => Carbon::now()->subMonths(20) ]);
        factory('App\MixpanelActions', 3)->create([ 'created_at' => Carbon::now()->subMonths(10) ]);
        factory('App\MixpanelActions', 5)->create([ 'created_at' => Carbon::now()->subMonths(8) ]);
        factory('App\MixpanelActions', 5)->create([ 'created_at' => Carbon::now()->subMonths(5) ]);
    
        //act
        $start_date = Carbon::now()->subMonths(10);
        $end_date = Carbon::now()->subMonths(8);
        $filtered_data = $this->analytics->filter('App\MixpanelActions')->setXAxis($start_date, $end_date, 'month')->getByUnit();

        //assert
        $this->assertEquals([3, 0, 5], $filtered_data);
    }

    /**
     * @test
     * it can return the appropriate values for the x axis for day type interval
     */
    public function it_can_return_the_appropriate_values_for_the_x_axis_for_day_type_interval()
    {
        //arrange
        $start_date = Carbon::now()->subDays(4);
        $end_date = Carbon::now()->subDays(1);
        
        //act
        $this->analytics->setXAxis($start_date, $end_date, 'day');
        $x_axis = $this->analytics->getXAxis();
        
        //assert
        $this->assertEquals('day', $x_axis['interval']);
        $this->assertEquals([Carbon::now()->subDays(4), Carbon::now()->subDays(3), Carbon::now()->subDays(2), Carbon::now()->subDays(1)], $x_axis['axis_points']);
    }

    /**
     * @test
     * it can return the appropriate values for the x axis for hour type interval
     */
    public function it_can_return_the_appropriate_values_for_the_x_axis_for_hour_type_interval()
    {
        //arrange
        $start_date = Carbon::now()->subHours(4);
        $end_date = Carbon::now();
    
        //act
        $this->analytics->setXAxis($start_date, $end_date, 'hour');
        $x_axis = $this->analytics->getXAxis();

        //assert
        $this->assertEquals('hour', $x_axis['interval']);
        $this->assertEquals([$start_date, Carbon::now()->subHours(3), Carbon::now()->subHours(2), Carbon::now()->subHours(1), $end_date], $x_axis['axis_points']);
    }
}

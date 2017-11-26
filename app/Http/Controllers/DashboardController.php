<?php

namespace App\Http\Controllers;

use App\Post;
use App\User;
use App\Analytics;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
	protected $analytics;
    protected $request;

	public function __construct(Analytics $analytics, Request $request)
	{
		$this->analytics = $analytics;
        $this->request = $request;
	}

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function dashboard()
    {
        // dd($this->request->all());
    	$analytics = [];

    	$this->analytics->setModel('App\User');
    	$analytics['total_users'] = $this->analytics->getCount();
    	$analytics['total_male_users'] = $this->analytics->where('sex', 1)->getCount();
    	$analytics['total_female_users'] = $this->analytics->where('sex', 2)->getCount();
        
        $start_date = $this->request->input('start_date') ? $this->request->input('start_date') : Carbon::now()->subDay(1);
        $end_date = $this->request->input('end_date') ? $this->request->input('end_date') : Carbon::now();
        $interval = $this->request->input('interval') ? $this->request->input('interval') : 'hour';

        $analytics['timed']['start_date'] = $start_date;
        $analytics['timed']['end_date'] = $end_date;
        $analytics['timed']['interval'] = $interval;
        $analytics['timed']['new_users'] = $this->analytics->setXAxis($start_date, $end_date, $interval)->getByUnit();
        $analytics['timed']['x_axis'] = $this->analytics->getXAxis();
        
    	$analytics['user_types']['collector'] = $this->analytics->where('user_type_id', 3)->getCount();
    	$analytics['user_types']['gallery'] = $this->analytics->where('user_type_id', 4)->getCount();
    	$analytics['user_types']['enthusiast'] = $this->analytics->where('user_type_id', 5)->getCount();
    	$analytics['user_types']['artist'] = $this->analytics->where('user_type_id', 6)->getCount();
    	$analytics['user_types']['art_professional'] = $this->analytics->where('user_type_id', 7)->getCount();
    	$analytics['user_types']['fair'] = $this->analytics->where('user_type_id', 8)->getCount();
    	$analytics['user_types']['public_institute'] = $this->analytics->where('user_type_id', 9)->getCount();
    	$analytics['user_types']['others'] = $this->analytics->where('user_type_id', 10)->getCount();

    	$this->analytics->setModel('App\UserType');
    	$analytics['total_user_types'] = $this->analytics->getCount();

    	$this->analytics->setModel('App\Post');
    	$analytics['total_posts'] = $this->analytics->getCount();
    	$analytics['total_public_posts'] = $this->analytics->where('is_public', 1)->getCount();
    	$analytics['total_private_posts'] = $this->analytics->where('is_public', 0)->getCount();
    	$analytics['total_posts_for_sale'] = $this->analytics->where('has_buy_btn', 1)->getCount();
    	$analytics['total_posts_with_artist'] = $this->analytics->whereNot('artist_id', null)->getCount();
    	$analytics['top_3_post_locations'] = $this->analytics->top('address_title')->whereNot('address_title', null)->whereNot('address_title', "")->limit(3)->get();
        $analytics['timed']['new_posts'] = $this->analytics->setXAxis($start_date, $end_date, $interval)->getByUnit();

    	$this->analytics->setModel('App\Message');
    	$analytics['total_messages_sent'] = $this->analytics->getCount();
        $analytics['timed']['new_messages'] = $this->analytics->setXAxis($start_date, $end_date, $interval)->getByUnit();

		$this->analytics->setModel('App\Follower');
    	$analytics['total_follows'] = $this->analytics->where('is_still_following', 1)->getCount();

    	$this->analytics->setModel('App\Like');
    	$analytics['total_likes'] = $this->analytics->getCount();
        $analytics['timed']['new_likes'] = $this->analytics->setXAxis($start_date, $end_date, $interval)->getByUnit();

    	$this->analytics->setModel('App\Pin');
    	$analytics['total_pins'] = $this->analytics->getCount();
        $analytics['timed']['new_pins'] = $this->analytics->setXAxis($start_date, $end_date, $interval)->getByUnit();
    	
    	$this->analytics->setModel('App\Comment');
    	$analytics['total_comments'] = $this->analytics->getCount();
        $analytics['timed']['new_comments'] = $this->analytics->setXAxis($start_date, $end_date, $interval)->getByUnit();

    	$this->analytics->setModel('App\Gallery');
    	$analytics['total_galleries'] = $this->analytics->getCount();

    	$this->analytics->setModel('App\Hashtag');
    	$analytics['total_hashtags'] = $this->analytics->getCount();

    	$this->analytics->setModel('App\ArtType');
    	$analytics['total_art_types'] = $this->analytics->getCount();

    	$this->analytics->setModel('App\News');
    	$analytics['total_news'] = $this->analytics->getCount();

    	$this->analytics->setModel('App\Event');
    	$analytics['total_events'] = $this->analytics->getCount();

    	$this->analytics->setModel('App\ReportedUser');
    	$analytics['total_reported_users'] = $this->analytics->getCount();

    	$this->analytics->setModel('App\BlockedUser');
    	$analytics['total_blocked_users'] = $this->analytics->getCount();

        return view('dashboard', compact('analytics'));
    }
}

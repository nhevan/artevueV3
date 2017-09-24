<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

abstract class SearchTestCase extends TestCase
{
	use DatabaseTransactions;

	protected $needle_string;
	protected $matches_needle_string;
	protected $less_than_needle_int;
	protected $needle_int;

	public function __construct()
	{
		$this->needle_int = '100';
		$this->less_than_needle_int = '50';

		$this->needle_string = '!@#clue*&^';
		$this->matches_needle_string = "123 {$this->needle_string} abc";
	}

	public function setUp()
    {
        parent::setUp();
    }

    public function checkSingularity(TestResponse $response)
    {
    	$this->assertEquals(1, sizeof($response->json()));
 	    $this->assertNotEquals(2, sizeof($response->json()));
    }

}

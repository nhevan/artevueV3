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
	protected $response;
	protected $needle;

    abstract public function setUpTestClassInfo();

	public function __construct()
	{
		$this->needle_int = '100';
		$this->less_than_needle_int = '50';

		$this->needle_string = '!@#clue*&^';
		$this->matches_needle_string = "123 {$this->needle_string} abc";
		$this->setUpTestClassInfo();
	}

	public function setUp()
    {
        parent::setUp();
    }

    public function search($needle)
    {
    	$this->needle = $needle;

    	return $this;
    }


    public function checkSingularity()
    {
    	$total = $this->response->json()['pagination']['total'];

    	$this->assertEquals(1, $total);
 	    $this->assertNotEquals(2, $total);

 	    return $this;
    }

    public function matchByField($field_name, $request_field_name = 'undefined')
    {
    	$field_value = $this->matches_needle_string;
    	if ($request_field_name === 'undefined') {
    		$request_field_name = $field_value;
    	}
    	$this->response = $this->callSearchEndPoint($field_name, $this->needle_string);
 	
 	    //assert
 	    $this->response->assertJsonFragment([
 	    		'id' => $this->needle->id,
 	    		"{$field_name}" => $this->matches_needle_string
 	    	]);

 	    return $this;
    }

    public function equalityByField($field_name, $request_field_name = 'undefined', $is_int = 0)
    {
    	$field_value = $this->matches_needle_string;
    	if ($request_field_name === 'undefined') {
    		$request_field_name = $field_value;
    	}

 	    if ($is_int) {
 	    	$field_value = (int) $this->needle_int;
	    	
	    	$this->response = $this->callSearchEndPoint($request_field_name, $this->needle_int);
 	    	
 	    	$this->response->assertJsonFragment([
 	    		'id' => $this->needle->id,
 	    		"{$field_name}" => $field_value
 	    	]);
 	    }
 	    $this->response = $this->callSearchEndPoint($request_field_name, $this->needle_string);
 	    	
 	    	$this->response->assertJsonFragment([
 	    		'id' => $this->needle->id,
 	    		"{$field_name}" => $field_value
 	    	]);

 	    return $this;
    }

    public function callSearchEndPoint($field_name, $field_value)
    {
    	return $this->json( 'GET', "/api/search-{$this->plural}", [
 				"{$field_name}" => $field_value
			]);
    }

}

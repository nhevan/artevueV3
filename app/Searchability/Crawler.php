<?php

namespace App\Searchability;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

abstract class Crawler
{
	protected $field_mapping = [
    	'primary_key' => [
    		'original_name' => 'id',
    		'condition' => '<>'
    	]
    ];
    public $rules = [
		            'price' => 'digits_between:0,99999999'
		        ];
    protected $request;
	protected $models;

	public function __construct(Request $request)
	{
		$this->request = $request;
		$this->setUp();
	}

	abstract public function setUp();

	public function search()
    {
    	$this->models = $this->searchByAllGivenParameters($this->models);

    	return $this->models->get();

    }
    

    public function searchByAllGivenParameters()
    {
    	$search_paramaters = $this->request->all();

    	foreach ($search_paramaters as $column_name => $value) {
    		if($this->isAlias($column_name)){
    			$this->models = $this->models->where($this->getOriginalColumn($column_name), $this->getCondition($column_name), $value);
    		}else{
	    		$this->models = $this->models->where($column_name, 'like', '%'.$value.'%');
    		}
    	}
    	return $this->models;
    }

	public function isAlias($column_name)
    {
    	if (isset($this->field_mapping[$column_name]['original_name'])) {
    		return true;
    	}

    	return false;
    }

    public function getOriginalColumn($column_name)
    {
    	return $this->field_mapping[$column_name]['original_name'];
    }

    public function getCondition($column_name)
    {
    	return $this->field_mapping[$column_name]['condition'];
    }

}

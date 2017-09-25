<?php

namespace App\Searchability;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

abstract class Crawler
{
    public $rules = [];
    protected $field_mapping = [];
    protected $request;
	protected $models;
	protected $model_fields;

	public function __construct(Request $request)
	{
		$this->request = $request;
		$this->setUp();
	}

	abstract public function setUp();

	public function search()
    {
    	$this->models = $this->searchByAllGivenParameters($this->models);

    	return $this->models->paginate(30);

    }
    

    public function searchByAllGivenParameters()
    {
    	$search_paramaters = $this->request->all();

    	foreach ($search_paramaters as $column_name => $value) {
            $this->searchByColumn($column_name, $value);
    	}
    	return $this->models;
    }

    public function searchByColumn($column_name, $value)
    {
        if($this->isAlias($column_name)){
            if ($this->getCondition($column_name)) {
                return $this->where($column_name, $value, $this->getCondition($column_name));
            }

            return $this->where($column_name, $value);
        }
    
        if ($this->isValidModelField($column_name)) {
            return $this->where($column_name, $value);
        }    
    }

    public function where($column, $value, $condition = null)
    {
        if ($condition) {
            $this->models = $this->models->where($this->getTargetColumn($column), $condition, $value);

            return;
        }
        
        $this->models = $this->models->where($this->getTargetColumn($column), 'like', '%'.$value.'%');        
    }



    public function isValidModelField($column_name)
    {
    	return in_array($column_name, DB::getSchemaBuilder()->getColumnListing($this->models->getTable()));
    }

	public function isAlias($column_name)
    {
    	if (isset($this->field_mapping[$column_name]['original_name'])) {
    		return true;
    	}

    	return false;
    }

    public function getTargetColumn($column_name)
    {
        if (isset($this->field_mapping[$column_name]['original_name'])) {
        	return $this->field_mapping[$column_name]['original_name'];
        }

        return $column_name;
    }

    public function getCondition($column_name)
    {
        if (isset($this->field_mapping[$column_name]['condition'])) {
        	return $this->field_mapping[$column_name]['condition'];
        }

        return null;
    }

}

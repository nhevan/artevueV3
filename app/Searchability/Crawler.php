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

    /**
     * is used define the model class
     */
	abstract public function setUp();

    /**
     * initiate search
     * @return [type] [description]
     */
	public function search()
    {
    	$this->models = $this->searchByAllGivenParameters($this->models);

    	return $this->models->paginate(30);

    }
    
    /**
     * searches a model with a given set of parameters
     * @return [type] [description]
     */
    public function searchByAllGivenParameters()
    {
    	$search_paramaters = $this->request->all();

    	foreach ($search_paramaters as $column_name => $value) {
            $this->searchByColumn($column_name, $value);
    	}
    	return $this->models;
    }

    /**
     * searches a column with a given value
     * @param  [type] $column_name [description]
     * @param  [type] $value       [description]
     * @return [type]              [description]
     */
    public function searchByColumn($column_name, $value)
    {
        if($this->isAlias($column_name)){
            if ($dedicatedMethod = $this->hasDedicatedWhereMethod($column_name)) {
                return $this->$dedicatedMethod($value);
            }

            if ($this->getCondition($column_name)) {
                return $this->where($column_name, $value, $this->getCondition($column_name));
            }

            return $this->where($column_name, $value);
        }
    
        if ($this->isValidModelField($column_name)) {
            if ($dedicatedMethod = $this->hasDedicatedWhereMethod($column_name)) {
                return $this->$dedicatedMethod($value);
            }
            return $this->where($column_name, $value);
        }    
    }

    /**
     * checks if the given column has a dedicated where method
     * @param  [type]  $column [description]
     * @return boolean         [description]
     */
    public function hasDedicatedWhereMethod($column)
    {
        $dedicatedFunctionName = camel_case('where'.title_case($column));
        $all_methods = get_class_methods(get_class($this));
        if (in_array($dedicatedFunctionName, $all_methods)) {
            return $dedicatedFunctionName;
        }
        return false;
    }

    /**
     * updates the query builder with a new where clause
     * @param  [type] $column    [description]
     * @param  [type] $value     [description]
     * @param  [type] $condition [description]
     * @return [type]            [description]
     */
    public function where($column, $value, $condition = null)
    {
        if ($condition) {
            $this->models = $this->models->where($this->getTargetColumn($column), $condition, $value);

            return;
        }
        
        $this->models = $this->models->where($this->getTargetColumn($column), 'like', '%'.$value.'%');        
    }


    /**
     * checks if the given column is an actual field of the model
     * @param  [type]  $column_name [description]
     * @return boolean              [description]
     */
    public function isValidModelField($column_name)
    {
    	return in_array($column_name, DB::getSchemaBuilder()->getColumnListing($this->models->getTable()));
    }

    /**
     * checks if a column name stands as an alias to a column
     * @param  [type]  $column_name [description]
     * @return boolean              [description]
     */
	public function isAlias($column_name)
    {
    	if (isset($this->field_mapping[$column_name]['original_name'])) {
    		return true;
    	}

    	return false;
    }

    /**
     * retrives the actual database column name from a column alias
     * @param  [type] $column_name [description]
     * @return [type]              [description]
     */
    public function getTargetColumn($alias)
    {
        if (isset($this->field_mapping[$alias]['original_name'])) {
        	return $this->field_mapping[$alias]['original_name'];
        }

        return $alias;
    }

    /**
     * fetches the associated condition with a column
     * @param  [type] $column_name [description]
     * @return [type]              [description]
     */
    public function getCondition($column_name)
    {
        if (isset($this->field_mapping[$column_name]['condition'])) {
        	return $this->field_mapping[$column_name]['condition'];
        }

        return null;
    }

}

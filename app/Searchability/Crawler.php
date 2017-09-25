<?php

namespace App\Searchability;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

abstract class Crawler
{
    public $rules = [
        // 'price' => 'digits_between:0,99999999'
    ];
    public $per_page_limit;
    protected $field_mapping = [
        // 'minimum_price' => [
        //  'field' => 'price',
        //  'condition' => '>='
        // ],
        // 'maximum_price' => [
        //  'field' => 'price',
        //  'condition' => '<=' 
        // ],
        // 'owner_username'
    ];
    protected $request;
	protected $model;
	protected $model_fields;
    protected $available_prefixes = [
        'max',
        'min', 
        'not'
    ];

	public function __construct(Request $request)
	{
		$this->request = $request;
        $this->per_page_limit = 30;
		$this->setUp();
	}

    /**
     * is used define the model class
     */
	abstract public function setUp();

    /**
     * is used to define the default conditions for a specific model
     * @return [type] [description]
     */
    public function defaultConditions(){}

    /**
     * initiate search
     * @return [type] [description]
     */
	public function search()
    {
    	$this->searchByAllGivenParameters()->defaultConditions();

    	return $this->model->paginate($this->per_page_limit);
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
    	return $this;
    }

    /**
     * returns false if the column name is not prefixed otherwise returns the prefix
     * @param  [type]  $column [description]
     * @return boolean         [description]
     */
    public function isPrefixed($column)
    {
        foreach ($this->available_prefixes as $prefix) {
            if (starts_with($column, $prefix.'_')) {
                return $prefix;
            }
        }
    }


    public function stripPrefix($key)
    {
        return str_after($key, $this->isPrefixed($key).'_');
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

            return $this->where($column_name, $value);
        }
    
        if ($this->isValidModelField($column_name)) {
            if ($dedicatedMethod = $this->hasDedicatedWhereMethod($column_name)) {
                return $this->$dedicatedMethod($value);
            }
            return $this->where($column_name, $value);
        }

        if ($prefix = $this->isPrefixed($column_name)) {
            if ($this->isValidModelField($this->stripPrefix($column_name))) {
                $prefixMethod = camel_case("where_".$prefix);
                return $this->$prefixMethod($column_name, $value);
            }
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
        if ($condition || $condition = $this->getCondition($column)) {
            $this->model = $this->model->where($this->getTargetColumn($column), $condition, $value);

            return $this;
        }
        
        $this->model = $this->model->where($this->getTargetColumn($column), 'like', '%'.$value.'%');

        return $this;     
    }

    public function whereMinimum($column, $value)
    {
        return $this->where($this->stripPrefix($column), $value, '>=');
    }
    public function whereMin($column, $value) {
        return $this->whereMinimum($column, $value);
    }

    public function whereMaximum($column, $value)
    {
        return $this->where($this->stripPrefix($column), $value, '<=');
    }
    public function whereMax($column, $value) {
        return $this->whereMaximum($column, $value);
    }

    public function whereNot($column, $value)
    {
        return $this->where($this->stripPrefix($column), $value, '<>');
    }


    /**
     * checks if the given column is an actual field of the model
     * @param  [type]  $column_name [description]
     * @return boolean              [description]
     */
    public function isValidModelField($column_name)
    {
    	return in_array($column_name, DB::getSchemaBuilder()->getColumnListing($this->model->getTable()));
    }

    /**
     * checks if a column name stands as an alias to a column
     * @param  [type]  $column_name [description]
     * @return boolean              [description]
     */
	public function isAlias($column_name)
    {
        return !! isset($this->field_mapping[$column_name]['field']);
    }

    /**
     * retrives the actual database column name from a column alias
     * @param  [type] $column_name [description]
     * @return [type]              [description]
     */
    public function getTargetColumn($alias)
    {
        if (isset($this->field_mapping[$alias]['field'])) {
        	return $this->field_mapping[$alias]['field'];
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

        return;
    }

}

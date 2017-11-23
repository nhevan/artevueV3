<?php

namespace App;

use Illuminate\Support\Facades\DB;

class Analytics
{
	protected $model;
	protected $query;
	/**
	 * sets the model where the analytics is suppose to run
	 * @param  [type] $model [description]
	 * @return [type]        [description]
	 */
    public function setModel($model)
    {
    	$this->model = new $model;
    	$this->query = $this->model;

    	return $this;
    }

    /**
     * returns the model the analytics is set to run on
     * @return [type] [description]
     */
    public function getModel()
    {
    	return $this->model;
    }

    /**
     * alias for setModel method
     * @param  [type] $model [description]
     * @return [type]        [description]
     */
    public function filter($model)
    {
    	return $this->setModel($model);
    }

    /**
     * adda a where query object to the query object for a given field name and value
     * @param  [type] $field_name [description]
     * @param  [type] $value      [description]
     * @return [type]             [description]
     */
    public function where($field_name, $value)
    {
    	$this->query = $this->query->where($field_name, $value);
    	
    	return $this;
    }

    /**
     * adds a where not query for a given field
     * @param  [type] $field_name [description]
     * @return [type]             [description]
     */
    public function whereNot($field_name, $value)
    {
        $this->query = $this->query->where($field_name, '<>', $value);
        
        return $this;
    }

    /**
     * returns the count for the filtered model entitites
     * @return [type] [description]
     */
    public function getCount()
    {
    	$count = $this->query->count();
        $this->query = $this->model;

        return $count;
    }

    /**
     * limits the resultset to given limit value
     * @param  [type] $limit [description]
     * @return [type]        [description]
     */
    public function limit($limit)
    {
        $this->query = $this->query->limit($limit);
        
        return $this;
    }

    /**
     * executes the current query by running the Eloquent get method
     * @return [type] [description]
     */
    public function get()
    {
        $current_query = $this->query;
        $this->query = $this->model;

        return $current_query->get();
    }

    /**
     * filters the top results for a given field
     * @return [type] [description]
     */
    public function top($field_name)
    {
        $this->query = $this->query->select([$field_name, DB::raw("COUNT(*) as count")])->groupBy($field_name)->orderByDesc('count');
        
        return $this;
    }
}

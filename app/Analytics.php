<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Analytics
{
	protected $model;
	protected $query;
    protected $axis;

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

    public function getXAxis()
    {
        return $this->axis;
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
     * adds a where query object to the query object for a given field name and value
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
     * filters the resultset by a given range for a specific field
     * @param  [type] $field_name  [description]
     * @param  [type] $start_value [description]
     * @param  [type] $end_value   [description]
     * @return [type]              [description]
     */
    public function whereBetween($field_name, $start_value, $end_value)
    {
        $this->query = $this->query->whereBetween($field_name, [ $start_value, $end_value ]);
        
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
     * returns a set of records in given units
     * @param  [type] $unit [description]
     * @return [type]       [description]
     */
    public function getByUnit()
    {
        $y_values = [];

        $i = 0;
        foreach ($this->axis['axis_points'] as $end_date) {
            if ($this->axis['interval'] == 'hour') {
                $start_date = $end_date->copy()->subHour();
            }

            if ($this->axis['interval'] == 'day') {
                $start_date = $end_date->copy()->subDay();
            }

            if ($this->axis['interval'] == 'month') {
                $start_date = $end_date->copy()->subMonth();
            }

            $record_count = $this->query->where('created_at', '>', $start_date)->where('created_at', '<=', $end_date)->count();

            array_push($y_values, $record_count);
        }

        $this->query = $this->model;

        return $y_values;
    }

    /**
     * formats the received unit like day, year to date format like 'd', 'Y' etc  
     * @param  [type] $unit [description]
     * @return [type]       [description]
     */
    public function getUnitInDateFormat($unit)
    {
        if ($unit == 'hour') {
            return 'h';
        }

        if ($unit == 'day') {
            return 'd';
        }

        if ($unit == 'month') {
            return 'm';
        }
    }

    public function getIntervalFunctions($interval)
    {
        if ($interval == 'hour') {
            return 'addHours';
        }

        if ($interval == 'day') {
            return 'addDays';
        }

        if ($interval == 'month') {
            return 'addMonths';
        }

    }

    public function getIntervalDiffFunctions($interval)
    {
        if ($interval == 'hour') {
            return 'diffInHours';
        }
        if ($interval == 'day') {
            return 'diffInDays';
        }
        if ($interval == 'month') {
            return 'diffInMonths';
        }
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

    /**
     * returns the x axis for a given range of dates and interval
     * @param  [type] $start_date [description]
     * @param  [type] $end_date   [description]
     * @param  [type] $interval   [description]
     * @return [type]             [description]
     */
    public function setXAxis($start_date, $end_date, $interval)
    {
        $start_date = Carbon::parse($start_date);
        $end_date = Carbon::parse($end_date);

        $intervalFunc = $this->getIntervalFunctions($interval);
        $intervalDifferenceFunc = $this->getIntervalDiffFunctions($interval);
        
        $i = 0;
        $axis['axis_points'] = [];
        $total_interval_slots = $start_date->copy()->$intervalDifferenceFunc($end_date);
        while ($i <= $total_interval_slots) {
            array_push($axis['axis_points'], $start_date->copy()->$intervalFunc($i));
            $i++;
        }

        $axis['interval'] = $interval;
        $axis['axis_label_format'] = $this->getXAxisLabelFormat($interval);
        $this->axis = $axis;

        return $this;
    }

    public function getXAxisLabelFormat($interval)
    {
        if ($interval == 'hour') {
            return 'ga';
        }

        if ($interval == 'day') {
            return 'D';
        }

        if ($interval == 'month') {
            return 'M-y';
        }
    }
}

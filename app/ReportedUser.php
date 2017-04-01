<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReportedUser extends Model
{
    protected $table = 'reported_users';

    protected $fillable = [
    	'user_id', 'suspect_id'
    ];
}

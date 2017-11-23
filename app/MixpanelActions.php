<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MixpanelActions extends Model
{
    protected $fillable = ['user_id', 'action', 'properties', 'ip'];
}

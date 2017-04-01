<?php

namespace App\Http\Controllers;

use App\User;
use App\ReportedUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportedUsersController extends ApiController
{
    /**
     * reports a user
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function store($user_id, Request $request)
    {
    	$user = User::find($user_id);
        if (!$user) {
            return $this->responseNotFound('User does not exist.');
        }

        if ($this->userHasAlreadyReported($user_id)) {
        	return $this->respond(['message'=> Auth::user()->name.' has already reported this user.']);
        }

        ReportedUser::create(['user_id' => Auth::user()->id, 'suspect_id' => $user_id]);
        
        return $this->respond(['message'=> Auth::user()->name.' has reported a user.']);
    }

    /**
     * checks whether the given user has already been reporte or not
     * @param  [type] $user_id [description]
     * @return [type]          [description]
     */
    public function userHasAlreadyReported($user_id)
    {
    	return ReportedUser::where(['user_id' => Auth::user()->id, 'suspect_id' => $user_id])->first();
    }
}

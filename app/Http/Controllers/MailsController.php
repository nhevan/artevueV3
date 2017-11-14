<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MailsController extends ApiController
{
    public function templates()
    {
    	return view('mails.templates');
    }
}

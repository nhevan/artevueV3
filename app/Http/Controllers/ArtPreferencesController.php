<?php

namespace App\Http\Controllers;

use App\ArtPreference;
use Illuminate\Http\Request;

class ArtPreferencesController extends ApiController
{
	protected $art_prefs;
	protected $request;

	public function __construct(ArtPreference $art_prefs,Request $request)
	{
		$this->art_prefs = $art_prefs;
		$this->request = $request;
	}

	/**
	 * returns a list of art preferences
	 * @return [type] [description]
	 */
    public function index()
    {
    	$art_prefs = $this->art_prefs->select(['id', 'title'])->get();

    	return $this->respond(['data' => $art_prefs]);
    }
}

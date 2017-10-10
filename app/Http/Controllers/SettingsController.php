<?php

namespace App\Http\Controllers;

use App\Settings;
use Illuminate\Http\Request;

class SettingsController extends ApiController
{
    protected $settings;

    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
    }

    /**
     * returns the current status of the system including latest app versions
     * @return [type] [description]
     */
    public function status()
    {
        $platform = strtolower(request()->header("X-ARTEVUE-App-Platform"));
        
        if ($platform == 'android') {
            $settings = $this->settings->where('key', 'LIKE', "%android%")->pluck('value', 'key')->toArray();    
            return response()->json($settings);
        }

        if ($platform == 'ios') {
            $settings = $this->settings->where('key', 'LIKE', "%ios%")->pluck('value', 'key')->toArray();    
            return response()->json($settings);
        }
        
        return $this
                    ->setStatusCode(422)
                    ->respondWithError("Please provide the X-ARTEVUE-App-Platform key with a valid value (iOS/Android) with the header.");
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $app_settings = $this->settings->where('key', 'like', '%app%')->get();
        $weight_settings = $this->settings->where('key', 'like', '%weight%')->get();

        return view('settings.dashboard', compact(['app_settings', 'weight_settings']));
    }

    public function editAppSettings()
    {
        $app_settings = $this->settings->where('key', 'like', '%app%')->get();
        
        return view('settings.edit-app-settings', compact('app_settings'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Settings  $settings
     * @return \Illuminate\Http\Response
     */
    public function show(Settings $settings)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Settings  $settings
     * @return \Illuminate\Http\Response
     */
    public function edit(Settings $settings)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Settings  $settings
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Settings $settings)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Settings  $settings
     * @return \Illuminate\Http\Response
     */
    public function destroy(Settings $settings)
    {
        //
    }
}

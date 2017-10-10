<?php

namespace App\Http\Controllers;

use App\Settings;
use Illuminate\Http\Request;

class SettingsController extends ApiController
{
    protected $settings;
    protected $request;

    public function __construct(Settings $settings, Request $request)
    {
        $this->settings = $settings;
        $this->request = $request;
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

    /**
     * update app settings
     * @return [type] [description]
     */
    public function editAppSettings()
    {
        if ($this->request->isMethod('get')) {
            $app_settings = $this->settings->where('key', 'like', '%app%')->get();

            return view('settings.edit-app-settings', compact('app_settings'));
        }

        $this->updateAllGivenSettingsValue();

        return redirect()->route('settings.index');
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

    /**
     * updates a specific field of a given settings
     * @param  [type] $key   [description]
     * @param  [type] $value [description]
     * @return [type]        [description]
     */
    public function updateSettingField($key, $value)
    {
        $setting_key = explode('-', $key)[0];
        $setting_field_name = explode('-', $key)[1];

        $setting = $this->settings->where('key', $setting_key)->first();
        $setting->$setting_field_name = $value;

        $setting->save();
    }

    /**
     * updates all the settings passed by the request variable
     * @return [type] [description]
     */
    public function updateAllGivenSettingsValue()
    {
        foreach ($this->request->except(['_token']) as $key => $value) {
            $this->updateSettingField($key, $value);
        }
    }
}

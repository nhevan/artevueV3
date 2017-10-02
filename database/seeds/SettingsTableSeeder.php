<?php

use App\Settings;
use Illuminate\Database\Seeder;

class SettingsTableSeeder extends Seeder
{
	protected $settings;

	public function __construct(Settings $settings)
	{
		$this->settings = $settings;
	}

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	$this->newSetting(	'ios_latest_app_version',						'1.3.4', 			'Currently Latest iOS app version, Please keep updated.' );
    	$this->newSetting(	'ios_min_app_version',							'1.3.4', 			'Minimum iOS app version required to continue using the app. Please keep updated.' );
        $this->newSetting(	'android_latest_app_version', 					'0', 				'Currently Latest Android app version, Please keep updated.' );
        $this->newSetting(	'android_min_app_version', 						'0', 				'Minimum Android app version required to continue using the app. Please keep updated.' );
        $this->newSetting(	'chronological_weight_distribution', 			'.25', 				'The chronological impact factor that will affect the Trending Art algorithm.' );        
        $this->newSetting(	'like_weight_distribution', 					'.75', 				'The like impact factor that will affect the Trending Art algorithm.' );
        $this->newSetting(	'pin_weight_distribution', 						'0.00001', 			'The pin impact factor that will affect the Trending Art algorithm.' );
    }

    /**
     * createa a new settings row only if a row with identical key does not exist
     * @param  [type] $key         settings key name
     * @param  [type] $value       settings key value
     * @param  string $description a short sentence trying to describe the settings
     * @param  string $message     an additional field to keep some additional message
     * @return [type]              [description]
     */
    public function newSetting($key, $value, $description = 'Sample description of the settings key', $message = 'You can set some additional info in the message field.')
    {
    	$set = $this->settings->where('key', $key)->first();
    	if (!$set) {
	        return factory('App\Settings')->create([
	        		'key' => $key,
			        'value' => $value,
			        'description' => $description,
			        'message' => $message
	        	]);
    	}
    }
}

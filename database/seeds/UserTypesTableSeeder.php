<?php

use App\UserType;
use Illuminate\Database\Seeder;

class UserTypesTableSeeder extends Seeder
{
	protected $user_types;

	public function __construct(UserType $user_types)
	{
		$this->user_types = $user_types;
	}
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->newUserType(1,	'Super Admin',			'For system admins only.');
        $this->newUserType(2,	'Admin',				'Users that has access to web dashboard with all the power to moderate the system.');
        $this->newUserType(3,	'Collector');
        $this->newUserType(4,	'Gallery');
        $this->newUserType(5,	'Enthusiast');
        $this->newUserType(6,	'Artist');
        $this->newUserType(7,	'Art Professional');
        $this->newUserType(8,	'Fair');
        $this->newUserType(9,	'Public Institution');
        $this->newUserType(10,	'Other');
    }

    public function newUserType($id, $title, $description = "sample description of the user type.")
    {
    	$set = $this->user_types->where('id', $id)->orWhere('title', $title)->first();
    	if (!$set) {
	        return factory('App\UserType')->create([
	        		'id' => $id,
			        'title' => $title,
			        'description' => $description
	        	]);
    	}
    }

}

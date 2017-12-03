<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(SettingsTableSeeder::class);
        $this->call(UserTypesTableSeeder::class);
        $this->call(EmailTemplatesSeeder::class);
        $this->call(PostArtTypesTableSeeder::class);
    }
}

<?php

namespace Tests;

use App\User;
use App\UserMetadata;
use Laravel\Passport\Passport;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    // use DatabaseMigrations;

    protected $user;

    public function setUp()
    {
        parent::setUp();
        $this->seed('PostArtTypesTableSeeder');
    }

    public function signIn($user = [])
    {
        if (!$user) {
            $user = factory(UserMetadata::class)->create(['is_account_private' => 0])->user;
        }
        
        $this->user = $user;
        
        Passport::actingAs($this->user);

        return $this;
    }
}

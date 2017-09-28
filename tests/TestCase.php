<?php

namespace Tests;

use App\User;
use App\UserMetadata;
use Laravel\Passport\Passport;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected $user;

    public function setUp()
    {
        parent::setUp();
        $this->serverVariables = [
            'Accept' => 'application/json'
        ];
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

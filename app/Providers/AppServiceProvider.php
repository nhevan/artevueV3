<?php

namespace App\Providers;

use App\User;
use Illuminate\Support\Facades\Queue;
use App\Notifications\QueuedJobFailed;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        
        Queue::failing(function (JobFailed $event) {
            $user = new User;
            $user->notify(new QueuedJobFailed());
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}

<?php

use App\UserType;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(App\User::class, function (Faker\Generator $faker) {
    static $password;
    $user_types = UserType::pluck('id');

    return [
        'name' => $faker->name,
        'username' => $faker->unique()->userName,
        'email' => $faker->unique()->safeEmail,
        'password' => $password ?: $password = bcrypt('secret'),
        'user_type_id' =>$faker->randomElement($user_types->toArray()),
        'remember_token' => str_random(10),
    ];
});

$factory->define(App\UserMetadata::class, function (Faker\Generator $faker) {
    return [
        'user_id' =>  function () {
            return factory(App\User::class)->create()->id;
        },
    ];
});

$factory->define(App\UserType::class, function (Faker\Generator $faker) {
    return [
        'title' => $faker->userName,
        'description' => $faker->paragraph(1)
    ];
});
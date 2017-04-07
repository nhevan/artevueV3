<?php

use App\News;
use App\Post;
use App\User;
use App\Artist;
use App\ArtType;
use App\Hashtag;
use App\UserType;
use App\ArtPreference;
use App\ArtInteraction;

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

$factory->define(App\ArtPreference::class, function (Faker\Generator $faker) {
    return [
        'title' => $faker->word
    ];
});

$factory->define(App\UserArtPreference::class, function (Faker\Generator $faker) {
	$preferences = ArtPreference::pluck('id');
	$users = User::pluck('id');

    return [
        'art_preference_id' => $faker->randomElement($preferences->toArray()),
        'user_id' => $faker->randomElement($users->toArray())
    ];
});

$factory->define(App\ArtInteraction::class, function (Faker\Generator $faker) {
    return [
        'title' => $faker->word
    ];
});

$factory->define(App\UserArtInteraction::class, function (Faker\Generator $faker) {
	$preferences = ArtInteraction::pluck('id');
	$users = User::pluck('id');

    return [
        'art_interaction_id' => $faker->randomElement($preferences->toArray()),
        'user_id' => $faker->randomElement($users->toArray())
    ];
});

$factory->define(App\ArtType::class, function (Faker\Generator $faker) {
    return [
        'title' => $faker->word
    ];
});

$factory->define(App\UserArtType::class, function (Faker\Generator $faker) {
	$art_types = ArtType::pluck('id');
	$users = User::pluck('id');

    return [
        'art_type_id' => $faker->randomElement($art_types->toArray()),
        'user_id' => $faker->randomElement($users->toArray())
    ];
});

$factory->define(App\Follower::class, function (Faker\Generator $faker) {
    $users = User::pluck('id');

    return [
        'user_id' => $faker->randomElement($users->toArray()),
        'follower_id' => $faker->randomElement($users->toArray())
    ];
});
$factory->define(App\BlockedUser::class, function (Faker\Generator $faker) {
    $users = User::pluck('id');

    return [
        'user_id' => $faker->randomElement($users->toArray()),
        'blocked_user_id' => $faker->randomElement($users->toArray())
    ];
});

$factory->define(App\ReportedUser::class, function (Faker\Generator $faker) {
    $users = User::pluck('id');

    return [
        'user_id' => $faker->randomElement($users->toArray()),
        'suspect_id' => $faker->randomElement($users->toArray())
    ];
});

$factory->define(App\Message::class, function (Faker\Generator $faker) {
    $users = User::pluck('id');

    return [
        'sender_id' => $faker->randomElement($users->toArray()),
        'receiver_id' => $faker->randomElement($users->toArray()),
        'message' => $faker->sentence(10)
    ];
});

$factory->define(App\Artist::class, function (Faker\Generator $faker) {
    return [
        'title' => $faker->name
    ];
});

$factory->define(App\Post::class, function (Faker\Generator $faker) {
    $users = User::pluck('id');
    $artists = Artist::pluck('id');
    return [
        'image' => $faker->sentence(1),
        'description' => $faker->sentence(1),
        'owner_id' => $faker->randomElement($users->toArray()),
        'artist_id' => $faker->randomElement($artists->toArray()),
    ];
});

$factory->state(App\Post::class, 'noArtist', function ($faker) {
    return [
        'artist_id' => null,
    ];
});

$factory->define(App\Hashtag::class, function ($faker) {
    return [
        'hashtag' => '#'.$faker->word,
    ];
});

$factory->define(App\PostHashtag::class, function ($faker) {
    $posts = Post::pluck('id');
    $hashtags = Hashtag::pluck('id');
    return [
        'post_id' => $faker->randomElement($posts->toArray()),
        'hashtag_id' => $faker->randomElement($hashtags->toArray()),
    ];
});

$factory->define(App\Tag::class, function ($faker) {
    $posts = Post::pluck('id');
    $users = User::pluck('id');
    return [
        'post_id' => $faker->randomElement($posts->toArray()),
        'user_id' => $faker->randomElement($users->toArray()),
        'username' => $faker->name,
    ];
});

$factory->define(App\News::class, function ($faker) {
    return [
        'headline' => $faker->sentence,
        'description' => $faker->sentence(1),
        'image' => $faker->sentence,
        'url' => $faker->sentence,
        'publish_date' => $faker->date
    ];
});
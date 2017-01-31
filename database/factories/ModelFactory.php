<?php

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
/**
 * @var $factory \Illuminate\Database\Eloquent\Factory
 */
$factory->define(
    \App\Models\User::class,
    function (Faker\Generator $faker) {
        return [
            'id'       => $faker->uuid,
            'username' => $faker->userName,
            'email'    => $faker->email,
            'active'   => $faker->boolean(90),
            'roles'    => [\App\Models\Role::ROLE_USER],
            'password' => Hash::make($faker->password())
        ];
    }
);

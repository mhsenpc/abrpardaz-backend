<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model;
use Faker\Generator as Faker;

$factory->define(\App\Models\Plan::class, function (Faker $faker) {
    return [
        'name' => $faker->colorName,
        'disk' => $faker->numberBetween(5,50),
        'ram'  => $faker->numberBetween(1,12),
        'vcpu' => $faker->numberBetween(1,20),
    ];
});

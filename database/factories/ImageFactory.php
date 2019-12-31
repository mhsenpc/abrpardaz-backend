<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Image;
use Faker\Generator as Faker;

$factory->define(Image::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'version' => $faker->numberBetween(1,6),
        'remote_id' => $faker->uuid,
        'min_disk' => $faker->numberBetween(1,10),
        'min_ram' => $faker->numberBetween(1,10),
    ];
});

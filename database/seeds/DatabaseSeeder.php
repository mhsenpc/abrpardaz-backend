<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(SampleUserLimitsSeeder::class);
        $this->call(RolesAndPermissionsSeeder::class);
        $this->call(SampleCategories::class);
        $this->call(SampleUserSeeder::class);
    }
}

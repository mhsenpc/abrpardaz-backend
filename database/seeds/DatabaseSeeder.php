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
        $this->call(FillSampleImages::class);
        $this->call(FillSamplePlans::class);
        $this->call(RolesAndPermissionsSeeder::class);
        $this->call(SampleUserSeeder::class);
    }
}

<?php

use Illuminate\Database\Seeder;

class FillSamplePlans extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(App\Models\Plan::class, 3)->create();
    }
}

<?php

use Illuminate\Database\Seeder;

class FillSampleImages extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(App\Models\Image::class, 3)->create();
    }
}

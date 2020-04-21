<?php

use App\Models\Category;
use Illuminate\Database\Seeder;

class SampleCategories extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories_count = Category::all()->count();
        if ($categories_count == 0) {
            Category::create(['name' => 'مالی']);
            Category::create(['name' => 'فنی']);
        }
    }
}

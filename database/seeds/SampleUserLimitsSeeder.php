<?php

use App\Models\UserLimit;
use Illuminate\Database\Seeder;

class SampleUserLimitsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $limits_count = UserLimit::all()->count();
        if ($limits_count == 0) {
            UserLimit::create([
                'default' => true,
                'name' => 'محدودیت استاندارد',
                'max_machines' => 11,
                'max_snapshots' => 30,
                'max_volumes_usage' => 1024,
            ]);
        }
    }
}

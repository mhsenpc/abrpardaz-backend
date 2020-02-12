<?php

use App\Models\UserGroup;
use Illuminate\Database\Seeder;

class SampleUserGroupsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        UserGroup::create([
            'default' => true,
            'name' => 'کاربر استاندارد',
            'max_machines' => 11,
            'max_snapshots' => 30,
            'max_volumes_size' => 1024,
        ]);
    }
}

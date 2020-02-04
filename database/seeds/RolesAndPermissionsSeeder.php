<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (Role::all()->count() == 0) {
            $normal_user = Role::create(['name' => 'Normal User']);
            $super_admin = Role::create(['name' => 'Super Admin']);

            Permission::create(['name' => 'List Users'])->assignRole($super_admin);
            Permission::create(['name' => 'Edit Users'])->assignRole($super_admin);
            Permission::create(['name' => 'Remove Users'])->assignRole($super_admin);
            Permission::create(['name' => 'Verify Users'])->assignRole($super_admin);
        }
    }
}

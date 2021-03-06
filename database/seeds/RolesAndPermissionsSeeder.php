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
            Permission::create(['name' => 'Add Users'])->assignRole($super_admin);
            Permission::create(['name' => 'Change User Limit'])->assignRole($super_admin);
            Permission::create(['name' => 'Change User Role'])->assignRole($super_admin);
            Permission::create(['name' => 'Remove Users'])->assignRole($super_admin);
            Permission::create(['name' => 'Verify Users'])->assignRole($super_admin);
            Permission::create(['name' => 'Suspend Users'])->assignRole($super_admin);
            Permission::create(['name' => 'Unsuspend Users'])->assignRole($super_admin);
            Permission::create(['name' => 'Login As User'])->assignRole($super_admin);

            Permission::create(['name' => 'Validate Profile'])->assignRole($super_admin);
            Permission::create(['name' => 'Validate Documents'])->assignRole($super_admin);

            Permission::create(['name' => 'Manage Images'])->assignRole($super_admin);
            Permission::create(['name' => 'Add Images'])->assignRole($super_admin);
            Permission::create(['name' => 'Edit Images'])->assignRole($super_admin);
            Permission::create(['name' => 'Sync Images'])->assignRole($super_admin);
            Permission::create(['name' => 'Remove Images'])->assignRole($super_admin);

            Permission::create(['name' => 'Manage Plans'])->assignRole($super_admin);
            Permission::create(['name' => 'Add Plans'])->assignRole($super_admin);
            Permission::create(['name' => 'Edit Plans'])->assignRole($super_admin);
            Permission::create(['name' => 'Sync Plans'])->assignRole($super_admin);
            Permission::create(['name' => 'Remove Plans'])->assignRole($super_admin);

            Permission::create(['name' => 'List User Limits'])->assignRole($super_admin);
            Permission::create(['name' => 'Add User Limits'])->assignRole($super_admin);
            Permission::create(['name' => 'Edit User Limits'])->assignRole($super_admin);
            Permission::create(['name' => 'Remove User Limits'])->assignRole($super_admin);

            Permission::create(['name' => 'List Roles'])->assignRole($super_admin);
            Permission::create(['name' => 'Add Roles'])->assignRole($super_admin);
            Permission::create(['name' => 'Edit Roles'])->assignRole($super_admin);
            Permission::create(['name' => 'Remove Roles'])->assignRole($super_admin);

            Permission::create(['name' => 'Ticket Operator'])->assignRole($super_admin);
            Permission::create(['name' => 'Invoice Operator'])->assignRole($super_admin);
        }
    }
}

<?php

use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SampleUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = User::newUser(
            'admin@admin.com',
            Hash::make('admin')
        );

        $admin->assignRole('Super Admin');

        $user = User::newUser(
            'user@user.com',
            Hash::make('user')
        );

        $user->assignRole('Normal User');
    }
}

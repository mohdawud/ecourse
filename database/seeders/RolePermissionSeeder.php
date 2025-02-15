<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //beberapa role
        //default user -> superadmin

        $ownerRole = Role::create([
            'name' => 'owner',
        ]);

        $studenRole = Role::create([
            'name' => 'student',
        ]);

        $teacherRole = Role::create([
            'name' => 'teacher',
        ]);

        //superadmin
        $userOwner = User::create([
            'name' => 'Dawud',
            'occupation' => 'Developer',
            'avatar' => 'images/avatar-default.png',
            'email' => 'daw@gmail.com',
            'password' => bcrypt('daw12345'),
        ]);

        $userOwner->assignRole($ownerRole);
    }
}

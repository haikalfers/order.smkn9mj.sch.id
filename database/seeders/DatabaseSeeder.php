<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Semua password default: "password"
     */
    public function run(): void
    {
        $users = [
            [
                'name'     => 'Super Admin',
                'email'    => 'superadmin@smkn9.sch.id',
                'password' => Hash::make('superadmin123'),
                'role'     => 'super_admin',
                'is_active'=> true,
            ],
            [
                'name'     => 'Admin',
                'email'    => 'admin@smkn9.sch.id',
                'password' => Hash::make('admin234'),
                'role'     => 'admin',
                'is_active'=> true,
            ],
        ];

        foreach ($users as $data) {
            User::updateOrCreate(['email' => $data['email']], $data);
        }
    }
}

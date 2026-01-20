<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
 
        User::create([
            'name' => 'Finance Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'keuangan',
            'bagian_id' => 1,
        ]);

        User::create([
            'name' => 'Admin Gudang General Administration',
            'email' => 'gudangTU@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'admin',
            'bagian_id' => 1,
        ]);

        User::create([
            'name' => 'Staff General Administration',
            'email' => 'userTU@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'user',
            'bagian_id' => 1,
        ]);

        User::create([
            'name' => 'Admin Gudang Survey and Mapping',
            'email' => 'gudangSP@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'admin',
            'bagian_id' => 2,
        ]);

        User::create([
            'name' => 'Staff Survey and Mapping',
            'email' => 'userSP@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'user',
            'bagian_id' => 2,
        ]);

        User::create([
            'name' => 'Admin Gudang Rights Determination and Registration',
            'email' => 'gudangPHP@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'admin',
            'bagian_id' => 3,
        ]);

        User::create([
            'name' => 'Staff Rights Determination and Registration',
            'email' => 'userPHP@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'user',
            'bagian_id' => 3,
        ]);

        User::create([
            'name' => 'Admin Gudang Planning and Empowerment',
            'email' => 'gudangPP@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'admin',
            'bagian_id' => 4,
        ]);

        User::create([
            'name' => 'Staff Planning and Empowerment',
            'email' => 'userPP@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'user',
            'bagian_id' => 4,
        ]);

        User::create([
            'name' => 'Admin Gudang Land Procurement and Development',
            'email' => 'gudangPTP@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'admin',
            'bagian_id' => 5,
        ]);

        User::create([
            'name' => 'Staff Land Procurement and Development',
            'email' => 'userPTP@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'user',
            'bagian_id' => 5,
        ]);

        User::create([
            'name' => 'Admin Gudang Dispute Control and Handling',
            'email' => 'gudangPPS@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'admin',
            'bagian_id' => 6,
        ]);

        User::create([
            'name' => 'Staff Dispute Control and Handling',
            'email' => 'userPPS@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'user',
            'bagian_id' => 6,
        ]);
    }
}
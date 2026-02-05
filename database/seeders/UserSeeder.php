<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{

    public function run(): void
    {
        $this->command->info('👥 Seeding Users...');

        $user1 = User::create([
            'name' => 'Finance Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'keuangan',
            'bagian_id' => 1,
        ]);
        $user1->assignRole('keuangan');

        $user2 = User::create([
            'name' => 'Admin Gudang General Administration',
            'email' => 'gudangTU@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'admin',
            'bagian_id' => 1,
        ]);
        $user2->assignRole('admin');

        $user3 = User::create([
            'name' => 'Staff General Administration',
            'email' => 'userTU@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'user',
            'bagian_id' => 1,
        ]);
        $user3->assignRole('user');

        $user4 = User::create([
            'name' => 'Admin Gudang Survey and Mapping',
            'email' => 'gudangSP@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'admin',
            'bagian_id' => 2,
        ]);
        $user4->assignRole('admin');

        $user5 = User::create([
            'name' => 'Staff Survey and Mapping',
            'email' => 'userSP@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'user',
            'bagian_id' => 2,
        ]);
        $user5->assignRole('user');

        $user6 = User::create([
            'name' => 'Admin Gudang Rights Determination and Registration',
            'email' => 'gudangPHP@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'admin',
            'bagian_id' => 3,
        ]);
        $user6->assignRole('admin');

        $user7 = User::create([
            'name' => 'Staff Rights Determination and Registration',
            'email' => 'userPHP@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'user',
            'bagian_id' => 3,
        ]);
        $user7->assignRole('user');

        $user8 = User::create([
            'name' => 'Admin Gudang Planning and Empowerment',
            'email' => 'gudangPP@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'admin',
            'bagian_id' => 4,
        ]);
        $user8->assignRole('admin');

        $user9 = User::create([
            'name' => 'Staff Planning and Empowerment',
            'email' => 'userPP@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'user',
            'bagian_id' => 4,
        ]);
        $user9->assignRole('user');

        $user10 = User::create([
            'name' => 'Admin Gudang Land Procurement and Development',
            'email' => 'gudangPTP@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'admin',
            'bagian_id' => 5,
        ]);
        $user10->assignRole('admin');

        $user11 = User::create([
            'name' => 'Staff Land Procurement and Development',
            'email' => 'userPTP@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'user',
            'bagian_id' => 5,
        ]);
        $user11->assignRole('user');

        $user12 = User::create([
            'name' => 'Admin Gudang Dispute Control and Handling',
            'email' => 'gudangPPS@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'admin',
            'bagian_id' => 6,
        ]);
        $user12->assignRole('admin');

        $user13 = User::create([
            'name' => 'Staff Dispute Control and Handling',
            'email' => 'userPPS@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'user',
            'bagian_id' => 6,
        ]);
        $user13->assignRole('user');

        $user14 = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'super_admin',
            'bagian_id' => 1,
        ]);
        $user14->assignRole('super_admin');

    }
}

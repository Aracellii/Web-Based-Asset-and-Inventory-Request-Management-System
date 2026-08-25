<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('👥 Seeding users...');

        // Get role IDs
        $financeRole = Role::where('name', 'finance')->first();
        $adminRole = Role::where('name', 'admin')->first();
        $userRole = Role::where('name', 'user')->first();
        $superAdminRole = Role::where('name', 'super_admin')->first();

        $user1 = User::create([
            'name' => 'Finance Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('12345678'),
            'role_id' => $financeRole->id,
            'bagian_id' => 1,
        ]);
        $user1->assignRole('finance');

        $user2 = User::create([
            'name' => 'Warehouse Admin - General Administration',
            'email' => 'gudangTU@gmail.com',
            'password' => Hash::make('12345678'),
            'role_id' => $adminRole->id,
            'bagian_id' => 1,
        ]);
        $user2->assignRole('admin');

        $user3 = User::create([
            'name' => 'General Administration Staff',
            'email' => 'userTU@gmail.com',
            'password' => Hash::make('12345678'),
            'role_id' => $userRole->id,
            'bagian_id' => 1,
        ]);
        $user3->assignRole('user');

        $user4 = User::create([
            'name' => 'Warehouse Admin - Survey and Mapping',
            'email' => 'gudangSP@gmail.com',
            'password' => Hash::make('12345678'),
            'role_id' => $adminRole->id,
            'bagian_id' => 2,
        ]);
        $user4->assignRole('admin');

        $user5 = User::create([
            'name' => 'Survey and Mapping Staff',
            'email' => 'userSP@gmail.com',
            'password' => Hash::make('12345678'),
            'role_id' => $userRole->id,
            'bagian_id' => 2,
        ]);
        $user5->assignRole('user');

        $user6 = User::create([
            'name' => 'Warehouse Admin - Rights Determination and Registration',
            'email' => 'gudangPHP@gmail.com',
            'password' => Hash::make('12345678'),
            'role_id' => $adminRole->id,
            'bagian_id' => 3,
        ]);
        $user6->assignRole('admin');

        $user7 = User::create([
            'name' => 'Rights Determination and Registration Staff',
            'email' => 'userPHP@gmail.com',
            'password' => Hash::make('12345678'),
            'role_id' => $userRole->id,
            'bagian_id' => 3,
        ]);
        $user7->assignRole('user');

        $user8 = User::create([
            'name' => 'Warehouse Admin - Planning and Empowerment',
            'email' => 'gudangPP@gmail.com',
            'password' => Hash::make('12345678'),
            'role_id' => $adminRole->id,
            'bagian_id' => 4,
        ]);
        $user8->assignRole('admin');

        $user9 = User::create([
            'name' => 'Planning and Empowerment Staff',
            'email' => 'userPP@gmail.com',
            'password' => Hash::make('12345678'),
            'role_id' => $userRole->id,
            'bagian_id' => 4,
        ]);
        $user9->assignRole('user');

        $user10 = User::create([
            'name' => 'Warehouse Admin - Land Procurement and Development',
            'email' => 'gudangPTP@gmail.com',
            'password' => Hash::make('12345678'),
            'role_id' => $adminRole->id,
            'bagian_id' => 5,
        ]);
        $user10->assignRole('admin');

        $user11 = User::create([
            'name' => 'Land Procurement and Development Staff',
            'email' => 'userPTP@gmail.com',
            'password' => Hash::make('12345678'),
            'role_id' => $userRole->id,
            'bagian_id' => 5,
        ]);
        $user11->assignRole('user');

        $user12 = User::create([
            'name' => 'Warehouse Admin - Dispute Control and Handling',
            'email' => 'gudangPPS@gmail.com',
            'password' => Hash::make('12345678'),
            'role_id' => $adminRole->id,
            'bagian_id' => 6,
        ]);
        $user12->assignRole('admin');

        $user13 = User::create([
            'name' => 'Dispute Control and Handling Staff',
            'email' => 'userPPS@gmail.com',
            'password' => Hash::make('12345678'),
            'role_id' => $userRole->id,
            'bagian_id' => 6,
        ]);
        $user13->assignRole('user');

        $user14 = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@gmail.com',
            'password' => Hash::make('12345678'),
            'role_id' => $superAdminRole->id,
            'bagian_id' => 1,
        ]);
        $user14->assignRole('super_admin');
    }
}

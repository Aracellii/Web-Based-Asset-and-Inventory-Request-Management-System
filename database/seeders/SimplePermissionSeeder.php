<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SimplePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ============================================
        // PERMISSIONS BERDASARKAN FITUR/MENU
        // ============================================
        
        $permissions = [
            // Dashboard
            'access_dashboard' => 'Akses Dashboard',
            
            // Stok Barang
            'access_stok_barang' => 'Akses Menu Stok Barang',
            'view_stok_barang' => 'Lihat Stok Barang',
            'manage_stok_barang' => 'Kelola Stok Barang (Tambah/Edit/Hapus)',
            'export_stok_barang' => 'Export Stok Barang',
            
            // Katalog Barang
            'access_katalog_barang' => 'Akses Menu Katalog Barang',
            'view_katalog_barang' => 'Lihat Katalog Barang',
            'manage_katalog_barang' => 'Kelola Katalog Barang (Tambah/Edit/Hapus)',
            'import_katalog_barang' => 'Import Katalog Barang',
            'export_katalog_barang' => 'Export Katalog Barang',
            
            // Permintaan
            'access_permintaan' => 'Akses Menu Permintaan',
            'view_permintaan' => 'Lihat Permintaan',
            'create_permintaan' => 'Buat Permintaan',
            'manage_permintaan' => 'Kelola Permintaan (Edit/Hapus)',
            'approve_permintaan' => 'Approve/Reject Permintaan',
            'export_permintaan' => 'Export Permintaan',
            
            // Log Aktivitas
            'access_log_aktivitas' => 'Akses Menu Log Aktivitas',
            'view_log_aktivitas' => 'Lihat Log Aktivitas',
            'export_log_aktivitas' => 'Export Log Aktivitas',
            'clear_log_aktivitas' => 'Hapus Log Aktivitas',
            
            // Manajemen User
            'access_manajemen_user' => 'Akses Menu Manajemen User',
            'view_manajemen_user' => 'Lihat User',
            'manage_manajemen_user' => 'Kelola User (Tambah/Edit/Hapus)',
            'export_manajemen_user' => 'Export Data User',
            
            // Settings & Roles (Admin only)
            'access_settings' => 'Akses Pengaturan',
            'manage_roles' => 'Kelola Roles & Permissions',
        ];

        // Create all permissions
        foreach ($permissions as $name => $description) {
            Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => 'web'
            ]);
        }

        // ============================================
        // ASSIGN PERMISSIONS KE ROLES
        // ============================================
        
        // SUPER ADMIN - Full Access
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(array_keys($permissions));

        // ADMIN - Manage semua kecuali settings
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions([
            'access_dashboard',
            
            'access_stok_barang',
            'view_stok_barang',
            'manage_stok_barang',
            'export_stok_barang',
            
            'access_katalog_barang',
            'view_katalog_barang',
            'manage_katalog_barang',
            'export_katalog_barang',
            
            'access_permintaan',
            'view_permintaan',
            'manage_permintaan',
            'approve_permintaan',
            'export_permintaan',
            
            'access_log_aktivitas',
            'view_log_aktivitas',
            'export_log_aktivitas',
            
            'access_manajemen_user',
            'view_manajemen_user',
            'manage_manajemen_user',
            'export_manajemen_user',
        ]);

        // KEUANGAN - Full access + approval authority
        $keuangan = Role::firstOrCreate(['name' => 'keuangan', 'guard_name' => 'web']);
        $keuangan->syncPermissions([
            'access_dashboard',
            
            'access_stok_barang',
            'view_stok_barang',
            'manage_stok_barang',
            'export_stok_barang',
            
            'access_katalog_barang',
            'view_katalog_barang',
            'manage_katalog_barang',
            'import_katalog_barang',
            'export_katalog_barang',
            
            'access_permintaan',
            'view_permintaan',
            'manage_permintaan',
            'approve_permintaan',
            'export_permintaan',
            
            'access_log_aktivitas',
            'view_log_aktivitas',
            'export_log_aktivitas',
            
            'access_manajemen_user',
            'view_manajemen_user',
        ]);

        // USER - Limited access
        $user = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);
        $user->syncPermissions([
            'access_dashboard',
            
            'access_stok_barang',
            'view_stok_barang',
            
            'access_katalog_barang',
            'view_katalog_barang',
            
            'access_permintaan',
            'view_permintaan',
            'create_permintaan',
            
            'access_log_aktivitas',
            'view_log_aktivitas',
        ]);

        $this->command->info('');
        $this->command->info('✅ Simple Permission System berhasil dibuat!');
        $this->command->info('');
        $this->command->table(
            ['Role', 'Permissions'],
            [
                ['super_admin', $superAdmin->permissions->count()],
                ['admin', $admin->permissions->count()],
                ['keuangan', $keuangan->permissions->count()],
                ['user', $user->permissions->count()],
            ]
        );
        $this->command->info('');
        $this->command->info('💡 Permission berdasarkan FITUR/MENU bukan per model CRUD');
    }
}

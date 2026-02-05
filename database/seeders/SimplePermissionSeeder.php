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

   
        $permissions = [
            // Dashboard
            'access_dashboard' => 'Akses Dashboard',
            
            // Dashboard Widgets
            'widget_KeuanganActivityStats' => 'Widget Aktivitas Keuangan (Dashboard)',
            'widget_AdminActivityStats' => 'Widget Aktivitas Admin (Dashboard)',
            'widget_UserActivityStats' => 'Widget Aktivitas User (Dashboard)',
            'widget_UserApproved' => 'Widget Barang Disetujui User (Dashboard)',
            'widget_StockMovementChart' => 'Widget Grafik Pergerakan Stok (Dashboard)',
            'widget_TopRequestedItemsChart' => 'Widget Grafik Barang Terbanyak Diminta (Dashboard)',
            
            // Scope Permissions - Batasan Data Bagian
            'lihat_bagian_sendiri' => 'Hanya Lihat Data Bagian Sendiri',
            'lihat_semua_bagian' => 'Lihat Data Semua Bagian',
            
            // Stok Barang
            'akses_stok' => 'Akses Menu Stok Barang',
            'view_stok_barang' => 'Lihat Stok Barang',
            'manage_stok_barang' => 'Kelola Stok Barang (Tambah/Edit/Hapus)',
            'export_stok_barang' => 'Export Stok Barang',
            
            // Katalog Barang
            'akses_katalog' => 'Akses Menu Katalog Barang',
            'view_katalog_barang' => 'Lihat Katalog Barang',
            'manage_katalog_barang' => 'Kelola Katalog Barang (Tambah/Edit/Hapus)',
            'import_katalog_barang' => 'Import Katalog Barang',
            'export_katalog_barang' => 'Export Katalog Barang',
            
            // Permintaan
            'akses_permintaan' => 'Akses Menu Permintaan',
            'view_permintaan' => 'Lihat Permintaan',
            'buat_permintaan' => 'Buat Permintaan',
            'manage_permintaan' => 'Kelola Permintaan (Edit/Hapus)',
            'approve_permintaan' => 'Approve/Reject Permintaan',
            'export_permintaan' => 'Export Permintaan',
            
            // Log Aktivitas
            'akses_log' => 'Akses Menu Log Aktivitas',
            'view_log_aktivitas' => 'Lihat Log Aktivitas',
            'export_log_aktivitas' => 'Export Log Aktivitas',
            'clear_log_aktivitas' => 'Hapus Log Aktivitas',
            
            // Manajemen User
            'akses_managemen_user' => 'Akses Menu Manajemen User',
            'view_manajemen_user' => 'Lihat User',
            'manage_manajemen_user' => 'Kelola User (Tambah/Edit/Hapus)',
            'export_manajemen_user' => 'Export Data User',
            
            // Settings & Roles (Admin only)
            'access_settings' => 'Akses Pengaturan',
            'manage_roles' => 'Kelola Roles & Permissions',
            
            // Shield RoleResource Permissions
            'view_role' => 'Lihat Role',
            'view_any_role' => 'Lihat Daftar Role',
            'create_role' => 'Buat Role',
            'update_role' => 'Edit Role',
            'delete_role' => 'Hapus Role',
            'delete_any_role' => 'Hapus Banyak Role',
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

        // ADMIN - Manage semua kecuali settings + Lihat Semua Bagian
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions([
            'access_dashboard',
            'widget_AdminActivityStats',
            'widget_StockMovementChart',
            'widget_TopRequestedItemsChart',
            'lihat_semua_bagian', // Admin bisa lihat semua bagian
            
            'akses_stok',
            'view_stok_barang',
            'manage_stok_barang',
            'export_stok_barang',
            
            'akses_katalog',
            'view_katalog_barang',
            'manage_katalog_barang',
            'export_katalog_barang',
            
            'akses_permintaan',
            'view_permintaan',
            'manage_permintaan',
            'approve_permintaan',
            'export_permintaan',
            
            'akses_log',
            'view_log_aktivitas',
            'export_log_aktivitas',
            
            'akses_managemen_user',
            'view_manajemen_user',
            'manage_manajemen_user',
            'export_manajemen_user',
            
            'manage_roles',
            'view_role',
            'view_any_role',
            'create_role',
            'update_role',
            'delete_role',
            'delete_any_role',
        ]);

        // KEUANGAN - Full access + approval authority + Lihat Semua Bagian
        $keuangan = Role::firstOrCreate(['name' => 'keuangan', 'guard_name' => 'web']);
        $keuangan->syncPermissions([
            'access_dashboard',
            'widget_KeuanganActivityStats',
            'widget_StockMovementChart',
            'widget_TopRequestedItemsChart',
            'lihat_semua_bagian', // Keuangan bisa lihat semua bagian
            
            'akses_stok',
            'view_stok_barang',
            'manage_stok_barang',
            'export_stok_barang',
            
            'akses_katalog',
            'view_katalog_barang',
            'manage_katalog_barang',
            'import_katalog_barang',
            'export_katalog_barang',
            
            'akses_permintaan',
            'view_permintaan',
            'manage_permintaan',
            'approve_permintaan',
            'export_permintaan',
            
            'akses_log',
            'view_log_aktivitas',
            'export_log_aktivitas',
            
            'akses_managemen_user',
            'view_manajemen_user',
            
            'manage_roles',
        ]);

        // USER - Limited access + Hanya Lihat Bagian Sendiri
        $user = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);
        $user->syncPermissions([
            'access_dashboard',
            'widget_UserActivityStats',
            'widget_UserApproved',
            'widget_TopRequestedItemsChart',
            'lihat_bagian_sendiri', // User hanya lihat bagian sendiri
            
            'akses_stok',
            'view_stok_barang',
            
            'akses_katalog',
            'view_katalog_barang',
            
            'akses_permintaan',
            'view_permintaan',
            'buat_permintaan',
            
            'akses_log',
            'view_log_aktivitas',
        ]);

        $this->command->table(
            ['Role', 'Permissions'],
            [
                ['super_admin', $superAdmin->permissions->count()],
                ['admin', $admin->permissions->count()],
                ['keuangan', $keuangan->permissions->count()],
                ['user', $user->permissions->count()],
            ]
        );
   
    }
}

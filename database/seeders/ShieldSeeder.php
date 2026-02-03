<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

/**
 * Shield Seeder - Permission & Role Management
 * 
 * ROLE STRUCTURE:
 * ---------------
 * 1. super_admin : Full access to all resources and features
 * 2. keuangan    : Finance admin - Approve/reject requests, view reports, manage master data
 * 3. admin       : Warehouse admin - Manage items, process requests per department
 * 4. user        : Staff - Create and view their own requests
 * 
 * AUTHORIZATION LAYERS:
 * ---------------------
 * Layer 1: Permission Check (via Spatie Permission)
 *   - User must have the permission (e.g., 'view_barang', 'update_gudang')
 *   - Checked automatically by Shield plugin
 * 
 * Layer 2: Query Scoping (via getEloquentQuery in Resources)
 *   - Filters which records user can see in list view
 *   - Super Admin & Keuangan: See all records
 *   - Admin: See only records from their department (bagian)
 *   - User: See only their own records
 * 
 * Layer 3: Row-Level Authorization (via Policies)
 *   - Determines if user can view/edit/delete specific record
 *   - Checked on individual actions (view, edit, delete buttons)
 *   - Provides fine-grained control per record
 * 
 * PERMISSION STRATEGY BY RESOURCE:
 * --------------------------------
 * UNDERSTANDING view vs view_any:
 *   - view: Melihat data yang terbatas (milik sendiri atau bagian sendiri)
 *   - view_any: Melihat SEMUA data dari semua bagian
 * 
 * Contoh di Gudang:
 *   - view_gudang: User bisa lihat stok gudang bagiannya saja
 *   - view_any_gudang: User bisa lihat stok semua gudang dari semua bagian
 * 
 * Barang (Items):
 *   - view/view_any: Difilter berdasarkan gudang bagian
 *   - create/update/delete: Only super_admin & keuangan
 * 
 * Gudang (Warehouse Stock):
 *   - view: Lihat gudang bagian sendiri
 *   - view_any: Lihat semua gudang
 *   - create/update: super_admin, keuangan, admin (admin only for their dept)
 *   - delete: super_admin, keuangan, admin (admin only for their dept)
 * 
 * Permintaan (Requests):
 *   - view: User lihat miliknya, Admin lihat bagiannya
 *   - view_any: Lihat semua permintaan
 *   - create: All roles
 *   - update: All roles (admin for approve/reject, user for own pending)
 *   - delete: user only (for own pending requests)
 * 
 * Detail Permintaan:
 *   - Same as Permintaan, tied to parent request
 * 
 * Log Aktivitas:
 *   - view: User lihat lognya sendiri, Admin lihat log bagiannya
 *   - view_any: Lihat semua log
 *   - create/update/delete: None (read-only, auto-generated)
 * 
 * CREDENTIALS:
 * ------------
 * Super Admin: superadmin@gmail.com / 12345678
 * Keuangan   : admin@gmail.com / 12345678
 * Admin      : gudangTU@gmail.com / 12345678
 * User       : userTU@gmail.com / 12345678
 * 
 * To reassign permissions, run:
 * php artisan db:seed --class=ShieldSeeder
 */
class ShieldSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. SUPER ADMIN - Full Access
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $allPermissions = Permission::all();
        $superAdmin->syncPermissions($allPermissions);

        // 2. KEUANGAN - Finance Admin (Verifikasi & Approval)
        // Dapat view_any untuk semua resource karena perlu monitor semua bagian
        $keuangan = Role::firstOrCreate(['name' => 'keuangan', 'guard_name' => 'web']);
        $keuangan->syncPermissions([
            // Permintaan - Approve/Reject semua bagian
            'view_permintaan',
            'view_any_permintaan', // Bisa lihat permintaan semua bagian
            'update_permintaan',
            
            // Detail Permintaan
            'view_detail::permintaan',
            'view_any_detail::permintaan', // Bisa lihat detail semua bagian
            
            // Barang - View semua barang
            'view_barang',
            'view_any_barang', // Bisa lihat semua barang
            
            // Gudang - View semua gudang
            'view_gudang',
            'view_any_gudang', // Bisa lihat stok semua gudang
            
            // Log Aktivitas - View semua log
            'view_log::aktivitas',
            'view_any_log::aktivitas', // Bisa lihat log semua aktivitas
            
            // Widgets
            'widget_KeuanganActivityStats',
            'widget_StockMovementChart',
            'widget_TopRequestedItemsChart',
        ]);

        // 3. ADMIN - Admin Gudang (Per Bagian)
        // Hanya dapat view (bagiannya), TIDAK dapat view_any
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions([
            // Barang - Lihat barang yang ada di gudang bagiannya
            'view_barang', // Hanya barang di gudang bagiannya
            // TIDAK dapat view_any_barang
            'create_barang',
            'update_barang',
            'delete_barang',
            'restore_barang',
            'replicate_barang',
            
            // Permintaan - Process permintaan dari bagiannya
            'view_permintaan', // Hanya permintaan dari bagiannya
            // TIDAK dapat view_any_permintaan
            'create_permintaan',
            'update_permintaan',
            
            // Detail Permintaan
            'view_detail::permintaan', // Hanya detail dari bagiannya
            // TIDAK dapat view_any_detail::permintaan
            'create_detail::permintaan',
            'update_detail::permintaan',
            'delete_detail::permintaan',
            
            // Gudang - Manage gudang bagiannya
            'view_gudang', // Hanya gudang bagiannya
            // TIDAK dapat view_any_gudang - ini yang penting!
            'create_gudang',
            'update_gudang',
            
            // Log Aktivitas - Lihat log bagiannya
            'view_log::aktivitas', // Hanya log dari bagiannya
            // TIDAK dapat view_any_log::aktivitas
            
            // Widgets
            'widget_AdminActivityStats',
            'widget_StockMovementChart',
            'widget_TopRequestedItemsChart',
        ]);

        // 4. USER - Staff (Hanya Buat Permintaan)
        // Hanya dapat view (milik sendiri), TIDAK dapat view_any
        $user = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);
        $user->syncPermissions([
            // Permintaan - Create & View Own saja
            'view_permintaan', // Hanya permintaannya sendiri
            // TIDAK dapat view_any_permintaan
            'create_permintaan',
            
            // Detail Permintaan - Create untuk permintaannya sendiri
            'view_detail::permintaan', // Hanya detail miliknya
            // TIDAK dapat view_any_detail::permintaan
            'create_detail::permintaan',
            
            // Barang - View barang yang ada di gudang bagiannya
            'view_barang', // Hanya barang di gudang bagiannya
            // TIDAK dapat view_any_barang
            
            // Gudang - View gudang bagiannya saja
            'view_gudang', // Hanya gudang bagiannya
            // TIDAK dapat view_any_gudang
            
            // Widgets
            'widget_UserActivityStats',
            'widget_UserApproved',
        ]);

        // Assign roles to existing users
        $users = \App\Models\User::all();
        foreach ($users as $user) {
            if ($user->role === 'keuangan') {
                $user->assignRole('keuangan');
            } elseif ($user->role === 'admin') {
                $user->assignRole('admin');
            } elseif ($user->role === 'user') {
                $user->assignRole('user');
            }
        }

        // Create Super Admin user if not exists
        $superAdminUser = \App\Models\User::firstOrCreate(
            ['email' => 'superadmin@gmail.com'],
            [
                'name' => 'Super Administrator',
                'password' => \Illuminate\Support\Facades\Hash::make('12345678'),
                'role' => 'super_admin',
                'bagian_id' => 1,
            ]
        );
        $superAdminUser->assignRole('super_admin');

        $this->command->info('✅ Roles and Permissions have been seeded successfully!');
        $this->command->info('📝 Super Admin: superadmin@gmail.com / 12345678');
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * 
     * Important order:
     * 1. DivisionSeeder - Users and warehouse stock need division_id
     * 2. ShieldSeeder - Users need roles
     * 3. UserSeeder - Assign roles to users
     * 4. ItemSeeder - Master item data
     * 5. WarehouseSeeder - Stock per division (needs item_id and division_id)
     * 6. RequestSeeder - Sample requests (needs user_id, item_id, division_id)
     * 7. ActivityLogSeeder - Sample logs (needs user_id, warehouse_id, item_id)
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting database seeding...');
        $this->command->newLine();
        
        $this->call([
            DivisionSeeder::class,              // 1. Set up 6 divisions
            ShieldSeeder::class,                // 2. Set up roles and permissions
            UserSeeder::class,                  // 3. Create 13 users
            ItemSeeder::class,                  // 4. Create 8 items
            WarehouseSeeder::class,             // 5. Create 48 warehouse stock records
            RequestSeeder::class,               // 6. Create sample requests (5 requests)
            ActivityLogSeeder::class,           // 7. Create sample activity logs (7 logs)
        ]);
        
        $this->command->newLine();
        $this->command->info('✅ Database seeding completed!');
        $this->command->info('📝 Login: admin@gmail.com / 12345678');
        $this->command->newLine();
    }
}

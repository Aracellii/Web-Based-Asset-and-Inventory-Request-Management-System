<?php

namespace Database\Seeders;

use App\Models\Bagian;
use Illuminate\Database\Seeder;

class DivisionSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('📁 Seeding divisions/work units...');
        
        $data = [
            ['id' => 1, 'nama_bagian' => 'General Administration'],
            ['id' => 2, 'nama_bagian' => 'Survey and Mapping'],
            ['id' => 3, 'nama_bagian' => 'Rights Determination and Registration'],
            ['id' => 4, 'nama_bagian' => 'Planning and Empowerment'],
            ['id' => 5, 'nama_bagian' => 'Land Procurement and Development'],
            ['id' => 6, 'nama_bagian' => 'Dispute Control and Handling'],
        ];

        foreach ($data as $item) {
            Bagian::create(['id' => $item['id'], 'nama_bagian' => $item['nama_bagian']]);
        }
        
    }
}
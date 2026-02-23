<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('branches')->insert([
            [
                'id' => 1,
                'name' => 'Sucursal Centro',
                'code' => 'CTR',
                'address' => 'Calle Principal 123',
                'phone' => '555-0101',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Sucursal Norte',
                'code' => 'NTE',
                'address' => 'Av. Norte 456',
                'phone' => '555-0202',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}

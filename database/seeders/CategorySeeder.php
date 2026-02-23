<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('categories')->insert([
            [
                'id' => 1,
                'name' => 'Bebidas',
                'description' => 'Bebidas y refrescos',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Snacks',
                'description' => 'Snacks y botanas',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}

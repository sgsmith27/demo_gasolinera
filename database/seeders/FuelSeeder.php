<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FuelSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $fuels = [
            ['name' => 'Gasolina Regular', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Gasolina Super',   'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Diesel',           'is_active' => true, 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('fuels')->upsert($fuels, ['name'], ['is_active', 'updated_at']);
    }
}
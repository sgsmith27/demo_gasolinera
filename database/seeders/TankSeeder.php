<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TankSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $fuels = DB::table('fuels')->select('id', 'name')->get();

        foreach ($fuels as $fuel) {
            DB::table('tanks')->updateOrInsert(
                ['fuel_id' => $fuel->id],
                [
                    'name' => 'Tanque - ' . $fuel->name,
                    'capacity_liters' => null,
                    'current_liters' => 0,
                    'is_active' => true,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }
}
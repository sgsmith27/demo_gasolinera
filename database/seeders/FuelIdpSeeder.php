<?php

namespace Database\Seeders;

use App\Models\Fuel;
use Illuminate\Database\Seeder;

class FuelIdpSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'Gasolina Super' => [
                'petroleo_code' => '1',
                'idp_amount_per_gallon' => 4.70,
            ],
            'Gasolina Regular' => [
                'petroleo_code' => '2',
                'idp_amount_per_gallon' => 4.60,
            ],
            'Diesel' => [
                'petroleo_code' => '4',
                'idp_amount_per_gallon' => 1.30,
            ],
        ];

        foreach ($data as $name => $values) {
            Fuel::query()
                ->where('name', $name)
                ->update($values);
        }
    }
}
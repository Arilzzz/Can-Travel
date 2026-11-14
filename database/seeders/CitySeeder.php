<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CitySeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $cities = [
                ['name' => 'Jakarta',     'province' => 'DKI Jakarta'],
                ['name' => 'Bandung',     'province' => 'Jawa Barat'],
                ['name' => 'Surabaya',    'province' => 'Jawa Timur'],
                ['name' => 'Yogyakarta',  'province' => 'DI Yogyakarta'],
                ['name' => 'Semarang',    'province' => 'Jawa Tengah'],
                ['name' => 'Malang',      'province' => 'Jawa Timur'],
                ['name' => 'Solo',        'province' => 'Jawa Tengah'],
                ['name' => 'Bogor',       'province' => 'Jawa Barat'],
                ['name' => 'Cirebon',     'province' => 'Jawa Barat'],
                ['name' => 'Tasikmalaya', 'province' => 'Jawa Barat'],
            ];

            foreach ($cities as $city) {
                City::create($city);
            }
        });

        $this->command->info('CitySeeder: 10 kota berhasil dibuat.');
    }
}

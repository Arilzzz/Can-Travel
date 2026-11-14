<?php

namespace Database\Seeders;

use App\Models\Route;
use App\Models\City;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RouteSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // Ambil ID kota berdasarkan nama
            $cities = City::pluck('id', 'name');

            $routes = [
                // Jakarta sebagai asal
                ['origin_city_id' => $cities['Jakarta'],    'destination_city_id' => $cities['Bandung']],
                ['origin_city_id' => $cities['Jakarta'],    'destination_city_id' => $cities['Surabaya']],
                ['origin_city_id' => $cities['Jakarta'],    'destination_city_id' => $cities['Yogyakarta']],
                ['origin_city_id' => $cities['Jakarta'],    'destination_city_id' => $cities['Semarang']],
                ['origin_city_id' => $cities['Jakarta'],    'destination_city_id' => $cities['Malang']],
                // Balik ke Jakarta
                ['origin_city_id' => $cities['Bandung'],    'destination_city_id' => $cities['Jakarta']],
                ['origin_city_id' => $cities['Surabaya'],   'destination_city_id' => $cities['Jakarta']],
                ['origin_city_id' => $cities['Yogyakarta'], 'destination_city_id' => $cities['Jakarta']],
                // Rute lainnya
                ['origin_city_id' => $cities['Surabaya'],   'destination_city_id' => $cities['Malang']],
                ['origin_city_id' => $cities['Malang'],     'destination_city_id' => $cities['Surabaya']],
                ['origin_city_id' => $cities['Yogyakarta'], 'destination_city_id' => $cities['Solo']],
                ['origin_city_id' => $cities['Solo'],       'destination_city_id' => $cities['Yogyakarta']],
                ['origin_city_id' => $cities['Semarang'],   'destination_city_id' => $cities['Surabaya']],
                ['origin_city_id' => $cities['Bandung'],    'destination_city_id' => $cities['Cirebon']],
            ];

            foreach ($routes as $route) {
                Route::create($route);
            }
        });

        $this->command->info('RouteSeeder: 14 rute berhasil dibuat.');
    }
}

<?php

namespace Database\Seeders;

use App\Models\Schedule;
use App\Models\Bus;
use App\Models\Route;
use App\Models\City;
use App\Models\Seat;
use App\Models\ScheduleSeat;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ScheduleSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $buses  = Bus::where('status', 'active')->get()->keyBy('bus_name');
            $cities = City::pluck('id', 'name');

            // Helper: cari route_id berdasarkan kota asal-tujuan
            $getRoute = function ($origin, $destination) use ($cities) {
                return Route::where('origin_city_id', $cities[$origin])
                    ->where('destination_city_id', $cities[$destination])
                    ->first()->id;
            };

            $schedules = [
                // === 20 November 2026 ===
                [
                    'bus'            => 'Sumber Kencono',
                    'origin'         => 'Jakarta',
                    'destination'    => 'Bandung',
                    'departure_time' => '2026-11-20 08:00:00',
                    'arrival_time'   => '2026-11-20 12:00:00',
                    'price'          => 150000,
                    'status'         => 'scheduled',
                ],
                [
                    'bus'            => 'Lorena Transport',
                    'origin'         => 'Jakarta',
                    'destination'    => 'Surabaya',
                    'departure_time' => '2026-11-20 20:00:00',
                    'arrival_time'   => '2026-11-21 06:00:00',
                    'price'          => 300000,
                    'status'         => 'scheduled',
                ],
                [
                    'bus'            => 'Pahala Kencana',
                    'origin'         => 'Jakarta',
                    'destination'    => 'Yogyakarta',
                    'departure_time' => '2026-11-20 19:00:00',
                    'arrival_time'   => '2026-11-21 05:00:00',
                    'price'          => 250000,
                    'status'         => 'scheduled',
                ],
                [
                    'bus'            => 'Harapan Jaya',
                    'origin'         => 'Jakarta',
                    'destination'    => 'Semarang',
                    'departure_time' => '2026-11-20 07:00:00',
                    'arrival_time'   => '2026-11-20 14:00:00',
                    'price'          => 200000,
                    'status'         => 'scheduled',
                ],
                // === 21 November 2026 ===
                [
                    'bus'            => 'Sumber Kencono',
                    'origin'         => 'Jakarta',
                    'destination'    => 'Bandung',
                    'departure_time' => '2026-11-21 10:00:00',
                    'arrival_time'   => '2026-11-21 14:00:00',
                    'price'          => 150000,
                    'status'         => 'scheduled',
                ],
                [
                    'bus'            => 'Kramat Djati',
                    'origin'         => 'Jakarta',
                    'destination'    => 'Malang',
                    'departure_time' => '2026-11-21 18:00:00',
                    'arrival_time'   => '2026-11-22 06:00:00',
                    'price'          => 320000,
                    'status'         => 'scheduled',
                ],
                [
                    'bus'            => 'Harapan Jaya',
                    'origin'         => 'Surabaya',
                    'destination'    => 'Malang',
                    'departure_time' => '2026-11-21 09:00:00',
                    'arrival_time'   => '2026-11-21 11:00:00',
                    'price'          => 75000,
                    'status'         => 'scheduled',
                ],
                // === Sudah completed (untuk test history) ===
                [
                    'bus'            => 'Lorena Transport',
                    'origin'         => 'Bandung',
                    'destination'    => 'Jakarta',
                    'departure_time' => '2026-11-10 08:00:00',
                    'arrival_time'   => '2026-11-10 12:00:00',
                    'price'          => 150000,
                    'status'         => 'completed',
                ],
            ];

            foreach ($schedules as $item) {
                $bus      = $buses[$item['bus']];
                $routeId  = $getRoute($item['origin'], $item['destination']);

                $schedule = Schedule::create([
                    'bus_id'         => $bus->id,
                    'route_id'       => $routeId,
                    'departure_time' => $item['departure_time'],
                    'arrival_time'   => $item['arrival_time'],
                    'price'          => $item['price'],
                    'status'         => $item['status'],
                ]);

                // Generate schedule_seats (ketersediaan kursi per jadwal)
                $seats = Seat::where('bus_id', $bus->id)->get();
                $scheduleSeatData = $seats->map(function ($seat) use ($schedule) {
                    return [
                        'schedule_id' => $schedule->id,
                        'seat_id'     => $seat->id,
                        'status'      => 'available',
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ];
                })->toArray();

                ScheduleSeat::insert($scheduleSeatData);
            }
        });

        $this->command->info('ScheduleSeeder: 8 jadwal + schedule_seats berhasil dibuat.');
    }
}

<?php

namespace Database\Seeders;

use App\Models\Bus;
use App\Models\Seat;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BusSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $buses = [
                [
                    'bus_name'     => 'Sumber Kencono',
                    'plate_number' => 'B 1234 KA',
                    'type'         => 'Executive',
                    'capacity'     => 40,
                    'status'       => 'active',
                ],
                [
                    'bus_name'     => 'Lorena Transport',
                    'plate_number' => 'B 5678 LO',
                    'type'         => 'Ekonomi',
                    'capacity'     => 50,
                    'status'       => 'active',
                ],
                [
                    'bus_name'     => 'Pahala Kencana',
                    'plate_number' => 'AD 9012 PK',
                    'type'         => 'Sleeper',
                    'capacity'     => 30,
                    'status'       => 'active',
                ],
                [
                    'bus_name'     => 'Harapan Jaya',
                    'plate_number' => 'W 3456 HJ',
                    'type'         => 'Executive',
                    'capacity'     => 40,
                    'status'       => 'active',
                ],
                [
                    'bus_name'     => 'Kramat Djati',
                    'plate_number' => 'B 7890 KD',
                    'type'         => 'Executive',
                    'capacity'     => 36,
                    'status'       => 'active',
                ],
                [
                    'bus_name'     => 'Rosalia Indah',
                    'plate_number' => 'AD 1122 RI',
                    'type'         => 'Ekonomi',
                    'capacity'     => 48,
                    'status'       => 'inactive', // bus sedang tidak aktif
                ],
            ];

            foreach ($buses as $busData) {
                $bus = Bus::create($busData);

                // Generate kursi otomatis (A01, A02, ... sesuai capacity)
                $seats = [];
                for ($i = 1; $i <= $busData['capacity']; $i++) {
                    $seats[] = [
                        'bus_id'      => $bus->id,
                        'seat_number' => 'A' . str_pad($i, 2, '0', STR_PAD_LEFT),
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ];
                }
                Seat::insert($seats);
            }
        });

        $this->command->info('BusSeeder: 6 bus + kursi otomatis berhasil dibuat.');
    }
}

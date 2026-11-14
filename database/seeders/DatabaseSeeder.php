<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,     // 1. Users (admin + customer)
            CitySeeder::class,     // 2. Kota
            BusSeeder::class,      // 3. Bus + Seats otomatis
            RouteSeeder::class,    // 4. Rute (butuh kota)
            ScheduleSeeder::class, // 5. Jadwal + ScheduleSeats (butuh bus & rute)
            BookingSeeder::class,  // 6. Booking dummy + BookingDetail + Payment
        ]);

        $this->command->info('');
        $this->command->info('✅ Semua seeder selesai!');
        $this->command->info('   Admin  : admin@cantravel.com / password123');
        $this->command->info('   Customer: budi@example.com / password123');
    }
}

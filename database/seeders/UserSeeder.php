<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // ============================================
            // ADMIN
            // ============================================
            User::create([
                'name'     => 'Admin CAN Travel',
                'email'    => 'admin@cantravel.com',
                'password' => 'password123', // auto hash via cast 'hashed'
                'role'     => 'admin',
            ]);

            // ============================================
            // CUSTOMER (data dummy)
            // ============================================
            $customers = [
                ['name' => 'Budi Santoso',   'email' => 'budi@example.com'],
                ['name' => 'Siti Rahayu',    'email' => 'siti@example.com'],
                ['name' => 'Ahmad Fauzi',    'email' => 'ahmad@example.com'],
                ['name' => 'Dewi Lestari',   'email' => 'dewi@example.com'],
                ['name' => 'Rudi Hermawan',  'email' => 'rudi@example.com'],
            ];

            foreach ($customers as $c) {
                User::create([
                    'name'     => $c['name'],
                    'email'    => $c['email'],
                    'password' => 'password123',
                    'role'     => 'customer',
                ]);
            }
        });

        $this->command->info('UserSeeder: 1 admin + 5 customer berhasil dibuat.');
    }
}

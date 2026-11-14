<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\BookingDetail;
use App\Models\Payment;
use App\Models\Schedule;
use App\Models\ScheduleSeat;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $budi  = User::where('email', 'budi@example.com')->first();
            $siti  = User::where('email', 'siti@example.com')->first();
            $ahmad = User::where('email', 'ahmad@example.com')->first();

            // ============================================
            // BOOKING 1: Budi - Jakarta->Bandung (PAID)
            // ============================================
            $sched1 = Schedule::first(); // jadwal pertama
            $seats1 = ScheduleSeat::where('schedule_id', $sched1->id)
                ->where('status', 'available')
                ->take(2)->get();

            $booking1 = Booking::create([
                'user_id'      => $budi->id,
                'schedule_id'  => $sched1->id,
                'booking_code' => 'BK-' . strtoupper(Str::random(8)),
                'total_price'  => $sched1->price * 2,
                'status'       => 'paid',
            ]);

            foreach ($seats1 as $i => $seat) {
                $seat->update(['status' => 'booked']);
                BookingDetail::create([
                    'booking_id'       => $booking1->id,
                    'schedule_seat_id' => $seat->id,
                    'passenger_name'   => $i === 0 ? 'Budi Santoso' : 'Istri Budi',
                    'passenger_phone'  => '0812345678' . $i,
                    'ticket_price'     => $sched1->price,
                ]);
            }

            // Pembayaran booking 1
            Payment::create([
                'booking_id'     => $booking1->id,
                'payment_method' => 'transfer',
                'amount'         => $booking1->total_price,
                'status'         => 'paid',
                'paid_at'        => now()->subDays(2),
            ]);

            // ============================================
            // BOOKING 2: Siti - Jakarta->Surabaya (PENDING)
            // ============================================
            $sched2 = Schedule::skip(1)->first();
            $seats2 = ScheduleSeat::where('schedule_id', $sched2->id)
                ->where('status', 'available')
                ->take(1)->get();

            $booking2 = Booking::create([
                'user_id'      => $siti->id,
                'schedule_id'  => $sched2->id,
                'booking_code' => 'BK-' . strtoupper(Str::random(8)),
                'total_price'  => $sched2->price * 1,
                'status'       => 'pending',
            ]);

            foreach ($seats2 as $seat) {
                $seat->update(['status' => 'booked']);
                BookingDetail::create([
                    'booking_id'       => $booking2->id,
                    'schedule_seat_id' => $seat->id,
                    'passenger_name'   => 'Siti Rahayu',
                    'passenger_phone'  => '08987654321',
                    'ticket_price'     => $sched2->price,
                ]);
            }

            // ============================================
            // BOOKING 3: Ahmad - Jakarta->Yogyakarta (CANCELLED)
            // ============================================
            $sched3 = Schedule::skip(2)->first();
            $seats3 = ScheduleSeat::where('schedule_id', $sched3->id)
                ->where('status', 'available')
                ->take(1)->get();

            $booking3 = Booking::create([
                'user_id'      => $ahmad->id,
                'schedule_id'  => $sched3->id,
                'booking_code' => 'BK-' . strtoupper(Str::random(8)),
                'total_price'  => $sched3->price,
                'status'       => 'cancelled',
            ]);

            // Kursi cancelled → tetap available
            foreach ($seats3 as $seat) {
                BookingDetail::create([
                    'booking_id'       => $booking3->id,
                    'schedule_seat_id' => $seat->id,
                    'passenger_name'   => 'Ahmad Fauzi',
                    'passenger_phone'  => '08123000111',
                    'ticket_price'     => $sched3->price,
                ]);
            }
        });

        $this->command->info('BookingSeeder: 3 booking dummy (paid/pending/cancelled) berhasil dibuat.');
    }
}


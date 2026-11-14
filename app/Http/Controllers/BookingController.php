<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingDetail;
use App\Models\Schedule;
use App\Models\ScheduleSeat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'schedule_id'                    => 'required|exists:schedules,id',
            'passengers'                     => 'required|array|min:1',
            'passengers.*.name'              => 'required|string|max:255',
            'passengers.*.phone'             => 'required|string|max:20',
            'passengers.*.schedule_seat_id'  => 'required|exists:schedule_seats,id',
        ], [
            'schedule_id.required'                   => 'Jadwal wajib dipilih.',
            'schedule_id.exists'                     => 'Jadwal tidak ditemukan.',
            'passengers.required'                    => 'Data penumpang wajib diisi.',
            'passengers.min'                         => 'Minimal 1 penumpang.',
            'passengers.*.name.required'             => 'Nama penumpang wajib diisi.',
            'passengers.*.phone.required'            => 'Nomor telepon penumpang wajib diisi.',
            'passengers.*.schedule_seat_id.required' => 'Kursi wajib dipilih.',
            'passengers.*.schedule_seat_id.exists'   => 'Kursi tidak ditemukan.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors'  => $validator->errors()
            ], 422);
        }

        $schedule = Schedule::find($request->schedule_id);
        if ($schedule->status !== 'scheduled') {
            return response()->json([
                'success' => false,
                'message' => 'Jadwal ini sudah tidak tersedia (status: ' . $schedule->status . ').',
            ], 422);
        }

        $scheduleSeatIds = collect($request->passengers)->pluck('schedule_seat_id');
        $seats = ScheduleSeat::whereIn('id', $scheduleSeatIds)->get();

        foreach ($seats as $seat) {
            if ($seat->schedule_id != $request->schedule_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kursi tidak sesuai dengan jadwal yang dipilih.',
                ], 422);
            }

            if ($seat->status !== 'available') {
                return response()->json([
                    'success' => false,
                    'message' => 'Kursi nomor ' . optional($seat->seat)->seat_number . ' sudah dipesan orang lain.',
                ], 422);
            }
        }

        $totalPrice = $schedule->price * count($request->passengers);

        $booking = DB::transaction(function () use ($request, $schedule, $totalPrice) {
            $booking = Booking::create([
                'user_id'      => $request->user()->id,
                'schedule_id'  => $request->schedule_id,
                'booking_code' => 'BK-' . strtoupper(Str::random(8)),
                'total_price'  => $totalPrice,
                'status'       => 'pending',
            ]);

            foreach ($request->passengers as $passenger) {
                ScheduleSeat::where('id', $passenger['schedule_seat_id'])
                    ->update(['status' => 'booked']);

                BookingDetail::create([
                    'booking_id'       => $booking->id,
                    'schedule_seat_id' => $passenger['schedule_seat_id'],
                    'passenger_name'   => $passenger['name'],
                    'passenger_phone'  => $passenger['phone'],
                    'ticket_price'     => $schedule->price,
                ]);
            }

            return $booking;
        });

        $booking->load([
            'schedule.route.originCity',
            'schedule.route.destinationCity',
            'schedule.bus',
            'bookingDetails.scheduleSeat.seat',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pemesanan berhasil! Kode booking: ' . $booking->booking_code,
            'data'    => $booking,
        ], 201);
    }

    public function index(Request $request)
    {
        $bookings = Booking::with([
            'schedule.route.originCity',
            'schedule.route.destinationCity',
            'schedule.bus',
        ])
        ->where('user_id', $request->user()->id)
        ->withCount('bookingDetails')
        ->orderBy('created_at', 'desc')
        ->get();

        return response()->json([
            'success' => true,
            'data'    => $bookings,
        ]);
    }

    public function show(Request $request, $id)
    {
        $booking = Booking::with([
            'schedule.route.originCity',
            'schedule.route.destinationCity',
            'schedule.bus',
            'bookingDetails.scheduleSeat.seat',
            'payment',
        ])
        ->where('user_id', $request->user()->id)
        ->find($id);

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Pemesanan tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $booking,
        ]);
    }

    public function cancel(Request $request, $id)
    {
        $booking = Booking::where('user_id', $request->user()->id)->find($id);

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Pemesanan tidak ditemukan.',
            ], 404);
        }

        if ($booking->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Pemesanan tidak bisa dibatalkan karena statusnya sudah: ' . $booking->status,
            ], 422);
        }

        DB::transaction(function () use ($booking) {
            $scheduleSeatIds = $booking->bookingDetails->pluck('schedule_seat_id');
            ScheduleSeat::whereIn('id', $scheduleSeatIds)->update(['status' => 'available']);
            $booking->update(['status' => 'cancelled']);
        });

        return response()->json([
            'success' => true,
            'message' => 'Pemesanan berhasil dibatalkan. Kursi sudah tersedia kembali.',
            'data'    => $booking->fresh(),
        ]);
        
    }
}

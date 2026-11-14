<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    public function index()
    {
        $bookings = Booking::with([
            'user',
            'schedule.route.originCity',
            'schedule.route.destinationCity',
            'schedule.bus',
        ])
        ->withCount('bookingDetails')
        ->orderBy('created_at', 'desc')
        ->get();

        return response()->json(['success' => true, 'data' => $bookings]);
    }

    public function updateStatus(Request $request, $id)
    {
        $booking = Booking::find($id);

        if (!$booking) {
            return response()->json(['success' => false, 'message' => 'Pemesanan tidak ditemukan.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,paid,cancelled,expired',
        ], [
            'status.required' => 'Status wajib diisi.',
            'status.in'       => 'Status harus: pending, paid, cancelled, atau expired.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors'  => $validator->errors()
            ], 422);
        }

        DB::transaction(function () use ($request, $booking) {
            $booking->update(['status' => $request->status]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Status pemesanan berhasil diperbarui menjadi: ' . $request->status,
            'data'    => $booking->fresh(),
        ]);
    }
}

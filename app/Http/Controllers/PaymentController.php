<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'booking_id'     => 'required|exists:bookings,id',
            'payment_method' => 'required|string|in:transfer,cash,qris,ovo,gopay',
            'amount'         => 'required|numeric|min:0',
        ], [
            'booking_id.required'     => 'ID booking wajib diisi.',
            'booking_id.exists'       => 'Booking tidak ditemukan.',
            'payment_method.required' => 'Metode pembayaran wajib dipilih.',
            'payment_method.in'       => 'Metode pembayaran tidak valid. Pilih: transfer, cash, qris, ovo, gopay.',
            'amount.required'         => 'Jumlah pembayaran wajib diisi.',
            'amount.numeric'          => 'Jumlah pembayaran harus berupa angka.',
            'amount.min'              => 'Jumlah pembayaran tidak boleh negatif.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors'  => $validator->errors()
            ], 422);
        }

        $booking = Booking::where('user_id', $request->user()->id)
            ->find($request->booking_id);

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking tidak ditemukan atau bukan milik Anda.',
            ], 404);
        }

        if ($booking->payment) {
            return response()->json([
                'success' => false,
                'message' => 'Booking ini sudah memiliki data pembayaran.',
            ], 422);
        }

        if ($booking->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Booking ini tidak bisa dibayar (status: ' . $booking->status . ').',
            ], 422);
        }

        if ($request->amount < $booking->total_price) {
            return response()->json([
                'success' => false,
                'message' => 'Pembayaran gagal! Jumlah bayar kurang dari total harga tiket (Rp ' . number_format($booking->total_price, 0, ',', '.') . ').',
            ], 422);
        }

        $payment = DB::transaction(function () use ($request, $booking) {
            $payment = Payment::create([
                'booking_id'     => $booking->id,
                'payment_method' => $request->payment_method,
                'amount'         => $request->amount,
                'status'         => 'paid',
                'paid_at'        => now(),
            ]);

            $booking->update(['status' => 'paid']);

            return $payment;
        });

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran berhasil! Tiket Anda sudah aktif.',
            'data'    => $payment->load('booking'),
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $payment = Payment::with('booking')->find($id);

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Data pembayaran tidak ditemukan.',
            ], 404);
        }

        if ($payment->booking->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Ini bukan pembayaran Anda.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data'    => $payment,
        ]);
    }
}

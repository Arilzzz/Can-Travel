<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\ScheduleSeat;
use App\Models\Seat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ScheduleController extends Controller
{
    public function index()
    {
        $schedules = Schedule::with([
            'bus',
            'route.originCity',
            'route.destinationCity',
        ])->orderBy('departure_time', 'desc')->get();

        return response()->json(['success' => true, 'data' => $schedules]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bus_id'         => 'required|exists:buses,id',
            'route_id'       => 'required|exists:routes,id',
            'departure_time' => 'required|date',
            'arrival_time'   => 'required|date|after:departure_time',
            'price'          => 'required|numeric|min:0',
            'status'         => 'required|in:active,scheduled,completed,cancelled',
        ], [
            'bus_id.required'         => 'Bus wajib dipilih.',
            'bus_id.exists'           => 'Bus tidak ditemukan.',
            'route_id.required'       => 'Rute wajib dipilih.',
            'route_id.exists'         => 'Rute tidak ditemukan.',
            'departure_time.required' => 'Waktu keberangkatan wajib diisi.',
            'departure_time.date'     => 'Format waktu keberangkatan tidak valid.',
            'arrival_time.required'   => 'Waktu kedatangan wajib diisi.',
            'arrival_time.after'      => 'Waktu kedatangan harus setelah waktu keberangkatan.',
            'price.required'          => 'Harga tiket wajib diisi.',
            'price.min'               => 'Harga tidak boleh negatif.',
            'status.in'               => 'Status harus: active, scheduled, completed, atau cancelled.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors'  => $validator->errors()
            ], 422);
        }

        $schedule = DB::transaction(function () use ($request) {
            $schedule = Schedule::create($request->all());

            $seats = Seat::where('bus_id', $request->bus_id)->get();

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

            return $schedule;
        });

        $schedule->load(['bus', 'route.originCity', 'route.destinationCity']);

        return response()->json([
            'success' => true,
            'message' => 'Jadwal berhasil ditambahkan.',
            'data'    => $schedule,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $schedule = Schedule::find($id);

        if (!$schedule) {
            return response()->json(['success' => false, 'message' => 'Jadwal tidak ditemukan.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'bus_id'         => 'required|exists:buses,id',
            'route_id'       => 'required|exists:routes,id',
            'departure_time' => 'required|date',
            'arrival_time'   => 'required|date|after:departure_time',
            'price'          => 'required|numeric|min:0',
            'status'         => 'required|in:active,scheduled,completed,cancelled',
        ], [
            'arrival_time.after' => 'Waktu kedatangan harus setelah waktu keberangkatan.',
            'status.in'          => 'Status harus: active, scheduled, completed, atau cancelled.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors'  => $validator->errors()
            ], 422);
        }

        DB::transaction(function () use ($request, $schedule) {
            $schedule->update($request->all());
        });

        $schedule->load(['bus', 'route.originCity', 'route.destinationCity']);

        return response()->json([
            'success' => true,
            'message' => 'Jadwal berhasil diperbarui.',
            'data'    => $schedule->fresh()->load(['bus', 'route.originCity', 'route.destinationCity']),
        ]);
    }

    public function destroy($id)
    {
        $schedule = Schedule::find($id);

        if (!$schedule) {
            return response()->json(['success' => false, 'message' => 'Jadwal tidak ditemukan.'], 404);
        }

        DB::transaction(function () use ($schedule) {
            $schedule->delete();
        });

        return response()->json(['success' => true, 'message' => 'Jadwal berhasil dihapus.']);
    }
}

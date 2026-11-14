<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'origin_city_id'      => 'nullable|exists:cities,id',
            'destination_city_id' => 'nullable|exists:cities,id',
            'date'                => 'nullable|date',
        ], [
            'origin_city_id.exists'      => 'Kota asal tidak ditemukan.',
            'destination_city_id.exists' => 'Kota tujuan tidak ditemukan.',
            'date.date'                  => 'Format tanggal tidak valid.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors'  => $validator->errors()
            ], 422);
        }

        $query = Schedule::with([
            'bus',
            'route.originCity',
            'route.destinationCity',
        ])->where('status', 'scheduled');

        if ($request->origin_city_id) {
            $query->whereHas('route', function ($q) use ($request) {
                $q->where('origin_city_id', $request->origin_city_id);
            });
        }

        if ($request->destination_city_id) {
            $query->whereHas('route', function ($q) use ($request) {
                $q->where('destination_city_id', $request->destination_city_id);
            });
        }

        if ($request->date) {
            $query->whereDate('departure_time', $request->date);
        }

        $schedules = $query->get();

        $schedules->each(function ($schedule) {
            $schedule->available_seats = $schedule->scheduleSeats()
                ->where('status', 'available')
                ->count();
        });

        return response()->json([
            'success' => true,
            'data'    => $schedules,
        ]);
    }

    public function show($id)
    {
        $schedule = Schedule::with([
            'bus',
            'route.originCity',
            'route.destinationCity',
        ])->find($id);

        if (!$schedule) {
            return response()->json([
                'success' => false,
                'message' => 'Jadwal tidak ditemukan.',
            ], 404);
        }

        $schedule->available_seats = $schedule->scheduleSeats()
            ->where('status', 'available')
            ->count();

        return response()->json([
            'success' => true,
            'data'    => $schedule,
        ]);
    }

    public function seats($id)
    {
        $schedule = Schedule::find($id);

        if (!$schedule) {
            return response()->json([
                'success' => false,
                'message' => 'Jadwal tidak ditemukan.',
            ], 404);
        }

        $seats = $schedule->scheduleSeats()
            ->with('seat')
            ->get()
            ->map(function ($scheduleSeat) {
                return [
                    'schedule_seat_id' => $scheduleSeat->id,
                    'seat_id'          => $scheduleSeat->seat_id,
                    'seat_number'      => $scheduleSeat->seat->seat_number,
                    'status'           => $scheduleSeat->status,
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => $seats,
        ]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bus;
use App\Models\Seat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BusController extends Controller
{
    public function index()
    {
        $buses = Bus::withCount('seats')->orderBy('id', 'desc')->get();

        return response()->json([
            'success' => true,
            'data'    => $buses,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'bus_name'     => 'required|string|max:255',
            'plate_number' => 'required|string|unique:buses,plate_number',
            'type'         => 'required|in:Executive,Ekonomi,Sleeper',
            'capacity'     => 'required|integer|min:1|max:100',
            'status'       => 'required|in:active,inactive',
        ], [
            'bus_name.required'     => 'Nama bus wajib diisi.',
            'plate_number.required' => 'Nomor kendaraan wajib diisi.',
            'plate_number.unique'   => 'Nomor kendaraan sudah terdaftar.',
            'type.required'         => 'Jenis bus wajib dipilih.',
            'type.in'               => 'Jenis bus harus: Executive, Ekonomi, atau Sleeper.',
            'capacity.required'     => 'Kapasitas wajib diisi.',
            'capacity.integer'      => 'Kapasitas harus berupa angka.',
            'capacity.min'          => 'Kapasitas minimal 1.',
            'status.required'       => 'Status wajib dipilih.',
            'status.in'             => 'Status harus: active atau inactive.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors'  => $validator->errors()
            ], 422);
        }

        $bus = DB::transaction(function () use ($request) {
            $bus = Bus::create($request->all());

            $seats = [];
            for ($i = 1; $i <= $request->capacity; $i++) {
                $seats[] = [
                    'bus_id'      => $bus->id,
                    'seat_number' => 'A' . str_pad($i, 2, '0', STR_PAD_LEFT),
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];
            }
            Seat::insert($seats);

            return $bus;
        });

        $bus->load('seats');

        return response()->json([
            'success' => true,
            'message' => 'Bus berhasil ditambahkan! ' . $request->capacity . ' kursi otomatis dibuat.',
            'data'    => $bus,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $bus = Bus::find($id);

        if (!$bus) {
            return response()->json(['success' => false, 'message' => 'Bus tidak ditemukan.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'bus_name'     => 'required|string|max:255',
            'plate_number' => 'required|string|unique:buses,plate_number,' . $id,
            'type'         => 'required|in:Executive,Ekonomi,Sleeper',
            'capacity'     => 'required|integer|min:1|max:100',
            'status'       => 'required|in:active,inactive',
        ], [
            'plate_number.unique' => 'Nomor kendaraan sudah terdaftar.',
            'type.in'             => 'Jenis bus harus: Executive, Ekonomi, atau Sleeper.',
            'status.in'           => 'Status harus: active atau inactive.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors'  => $validator->errors()
            ], 422);
        }

        DB::transaction(function () use ($request, $bus) {
            $bus->update($request->all());
        });

        return response()->json([
            'success' => true,
            'message' => 'Data bus berhasil diperbarui.',
            'data'    => $bus->fresh(),
        ]);
    }

    public function destroy($id)
    {
        $bus = Bus::find($id);

        if (!$bus) {
            return response()->json(['success' => false, 'message' => 'Bus tidak ditemukan.'], 404);
        }

        DB::transaction(function () use ($bus) {
            $bus->delete();
        });

        return response()->json(['success' => true, 'message' => 'Bus berhasil dihapus.']);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RouteController extends Controller
{
    public function index()
    {
        $routes = Route::with(['originCity', 'destinationCity'])->get();

        return response()->json(['success' => true, 'data' => $routes]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'origin_city_id'      => 'required|exists:cities,id',
            'destination_city_id' => 'required|exists:cities,id|different:origin_city_id',
        ], [
            'origin_city_id.required'       => 'Kota asal wajib dipilih.',
            'origin_city_id.exists'         => 'Kota asal tidak ditemukan.',
            'destination_city_id.required'  => 'Kota tujuan wajib dipilih.',
            'destination_city_id.exists'    => 'Kota tujuan tidak ditemukan.',
            'destination_city_id.different' => 'Kota tujuan tidak boleh sama dengan kota asal.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors'  => $validator->errors()
            ], 422);
        }

        $route = DB::transaction(function () use ($request) {
            return Route::create($request->all());
        });

        $route->load(['originCity', 'destinationCity']);

        return response()->json([
            'success' => true,
            'message' => 'Rute berhasil ditambahkan.',
            'data'    => $route,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $route = Route::find($id);

        if (!$route) {
            return response()->json(['success' => false, 'message' => 'Rute tidak ditemukan.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'origin_city_id'      => 'required|exists:cities,id',
            'destination_city_id' => 'required|exists:cities,id|different:origin_city_id',
        ], [
            'destination_city_id.different' => 'Kota tujuan tidak boleh sama dengan kota asal.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors'  => $validator->errors()
            ], 422);
        }

        DB::transaction(function () use ($request, $route) {
            $route->update($request->all());
        });

        $route->load(['originCity', 'destinationCity']);

        return response()->json([
            'success' => true,
            'message' => 'Rute berhasil diperbarui.',
            'data'    => $route->fresh()->load(['originCity', 'destinationCity']),
        ]);
    }

    public function destroy($id)
    {
        $route = Route::find($id);

        if (!$route) {
            return response()->json(['success' => false, 'message' => 'Rute tidak ditemukan.'], 404);
        }

        DB::transaction(function () use ($route) {
            $route->delete();
        });

        return response()->json(['success' => true, 'message' => 'Rute berhasil dihapus.']);
    }
}

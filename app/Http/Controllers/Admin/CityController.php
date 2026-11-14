<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CityController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'province' => 'required|string|max:255',
        ], [
            'name.required'     => 'Nama kota wajib diisi.',
            'province.required' => 'Provinsi wajib diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors'  => $validator->errors()
            ], 422);
        }

        $city = DB::transaction(function () use ($request) {
            return City::create($request->all());
        });

        return response()->json([
            'success' => true,
            'message' => 'Kota berhasil ditambahkan.',
            'data'    => $city,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $city = City::find($id);

        if (!$city) {
            return response()->json(['success' => false, 'message' => 'Kota tidak ditemukan.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'province' => 'required|string|max:255',
        ], [
            'name.required'     => 'Nama kota wajib diisi.',
            'province.required' => 'Provinsi wajib diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors'  => $validator->errors()
            ], 422);
        }

        DB::transaction(function () use ($request, $city) {
            $city->update($request->all());
        });

        return response()->json([
            'success' => true,
            'message' => 'Data kota berhasil diperbarui.',
            'data'    => $city->fresh(),
        ]);
    }

    public function destroy($id)
    {
        $city = City::find($id);

        if (!$city) {
            return response()->json(['success' => false, 'message' => 'Kota tidak ditemukan.'], 404);
        }

        DB::transaction(function () use ($city) {
            $city->delete();
        });

        return response()->json(['success' => true, 'message' => 'Kota berhasil dihapus.']);
    }
}

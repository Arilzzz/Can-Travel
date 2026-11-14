<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'name.required'      => 'Nama wajib diisi.',
            'email.required'     => 'Email wajib diisi.',
            'email.email'        => 'Format email tidak valid.',
            'email.unique'       => 'Email sudah terdaftar, gunakan email lain.',
            'password.required'  => 'Password wajib diisi.',
            'password.min'       => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors'  => $validator->errors()
            ], 422);
        }

        $result = DB::transaction(function () use ($request) {
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => $request->password,
                'role'     => 'user',
            ]);
            $token = $user->createToken('auth_token')->plainTextToken;
            return ['user' => $user, 'token' => $token];
        });

        return response()->json([
            'success' => true,
            'message' => 'Registrasi berhasil! Selamat datang, ' . $result['user']->name,
            'data'    => [
                'user'     => $result['user'],
                'token'    => $result['token'],
                'role'     => $result['user']->role,
                'is_admin' => false,
            ]
        ], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|string',
        ], [
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors'  => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password salah.',
            ], 401);
        }

        $token = DB::transaction(function () use ($user) {
            $user->tokens()->delete();
            return $user->createToken('auth_token')->plainTextToken;
        });

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil! Selamat datang, ' . $user->name,
            'data'    => [
                'user'     => $user,
                'token'    => $token,
                'role'     => $user->role,
                'is_admin' => $user->role === 'admin',
            ]
        ]);
    }

    public function logout(Request $request)
    {
        DB::transaction(function () use ($request) {
            $request->user()->currentAccessToken()->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil. Sampai jumpa!',
        ]);
    }

    public function profile(Request $request)
    {
        return response()->json([
            'success' => true,
            'data'    => $request->user(),
        ]);
    }
}

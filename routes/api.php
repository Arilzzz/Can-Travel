<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\BusController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\PaymentController;

use App\Http\Controllers\Admin\BusController as AdminBusController;
use App\Http\Controllers\Admin\CityController as AdminCityController;
use App\Http\Controllers\Admin\RouteController as AdminRouteController;
use App\Http\Controllers\Admin\ScheduleController as AdminScheduleController;
use App\Http\Controllers\Admin\BookingController as AdminBookingController;

/*
|--------------------------------------------------------------------------
| API Routes - CAN Travel
|--------------------------------------------------------------------------
*/

// AUTH ROUTES (tidak perlu login)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

// ROUTES YANG BUTUH LOGIN (auth:sanctum)
Route::middleware('auth:sanctum')->group(function () {

    // Info user yang sedang login
    Route::get('/user',   [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // CUSTOMER ROUTES

    // Daftar kota
    Route::get('/cities', [CityController::class, 'index']);

    // Daftar bus aktif
    Route::get('/buses', [BusController::class, 'index']);

    // Jadwal bus (dengan pencarian)
    Route::get('/schedules',              [ScheduleController::class, 'index']);
    Route::get('/schedules/{id}',         [ScheduleController::class, 'show']);
    Route::get('/schedules/{id}/seats',   [ScheduleController::class, 'seats']);

    // Pemesanan tiket
    Route::post('/bookings',              [BookingController::class, 'store']);
    Route::get('/bookings',               [BookingController::class, 'index']);
    Route::get('/bookings/{id}',          [BookingController::class, 'show']);
    Route::patch('/bookings/{id}/cancel', [BookingController::class, 'cancel']);

    // Pembayaran (simulasi)
    Route::post('/payments',    [PaymentController::class, 'store']);
    Route::get('/payments/{id}', [PaymentController::class, 'show']);

    // ADMIN ROUTES (butuh login + role admin)
    Route::middleware('admin')->prefix('admin')->group(function () {

        // Kelola Bus
        Route::get('/buses',        [AdminBusController::class, 'index']);
        Route::post('/buses',       [AdminBusController::class, 'store']);
        Route::put('/buses/{id}',   [AdminBusController::class, 'update']);
        Route::delete('/buses/{id}', [AdminBusController::class, 'destroy']);

        // Kelola Kota
        Route::post('/cities',       [AdminCityController::class, 'store']);
        Route::put('/cities/{id}',   [AdminCityController::class, 'update']);
        Route::delete('/cities/{id}', [AdminCityController::class, 'destroy']);

        // Kelola Rute
        Route::get('/routes',        [AdminRouteController::class, 'index']);
        Route::post('/routes',       [AdminRouteController::class, 'store']);
        Route::put('/routes/{id}',   [AdminRouteController::class, 'update']);
        Route::delete('/routes/{id}', [AdminRouteController::class, 'destroy']);

        // Kelola Jadwal
        Route::get('/schedules',          [AdminScheduleController::class, 'index']);
        Route::post('/schedules',         [AdminScheduleController::class, 'store']);
        Route::put('/schedules/{id}',     [AdminScheduleController::class, 'update']);
        Route::delete('/schedules/{id}',  [AdminScheduleController::class, 'destroy']);

        // Semua pemesanan (admin lihat semua)
        Route::get('/bookings',                    [AdminBookingController::class, 'index']);
        Route::patch('/bookings/{id}/status',      [AdminBookingController::class, 'updateStatus']);
    });
});

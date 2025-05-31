<?php

use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\BookingLogController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\ResourceTypeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

Route::post("/login", [AuthenticationController::class, 'login'])->name('login');
Route::middleware('auth:api')->group(function () {
    Route::post("/logout", [AuthenticationController::class, 'logout']);

    Route::middleware('role:admin')->group(function () {
        Route::post('/add', [AuthenticationController::class, 'addMember']);
        Route::apiResource('resource', ResourceController::class);
        Route::apiResource('resource-types', ResourceTypeController::class);
        Route::get('/book', [BookingController::class, 'index']);
        Route::get('/booking-logs', [BookingLogController::class, 'index']);
    });

    Route::middleware('role:faculty|student')->group(function () {
        Route::apiResource('book', BookingController::class)->except(['index']);
        Route::get('/own-bookings', [BookingController::class, 'showOwnBooking']);
        Route::get('/own-booking-logs', [BookingLogController::class, 'showOwnBookingLog']);
        Route::post('/booking-logs', [BookingLogController::class, 'store']);
    });
});

<?php

use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\BookingLogController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\ResourceTypeController;
use Illuminate\Support\Facades\Route;

Route::get('/auth/verify-email/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->middleware('signed')
    ->name('verification.verify');

Route::post('/auth/set-password', [EmailVerificationController::class, 'setPassword']);

Route::post("/login", [AuthenticationController::class, 'login'])->name('login');
Route::middleware(['auth:api', 'verified'])->group(function () {
    Route::post("/logout", [AuthenticationController::class, 'logout']);
    Route::get('/bookings/by-date', [BookingController::class, 'getBookingsByDate']);

    Route::middleware('role:admin')->group(function () {
        Route::post('/add', [AuthenticationController::class, 'addMember']);
        Route::apiResource('resource', ResourceController::class);
        Route::apiResource('resource-types', ResourceTypeController::class);
    });

    Route::middleware('role:admin|computer_lab_supervisor|library_supervisor|venue_hall_supervisor|sports_equipment_supervisor')->group(function () {
        Route::get('/book', [BookingController::class, 'index']);
        Route::get('/booking-logs', [BookingLogController::class, 'index']);
        Route::post('/{book}/approve', [BookingController::class, 'approve']);
        Route::post('/{book}/reject', [BookingController::class, 'reject']);
    });

    Route::middleware('role:faculty|student')->group(function () {
        Route::get('/resourceUsers', [ResourceController::class, 'resourceUsers']);
        Route::apiResource('book', BookingController::class)->except(['index', 'destroy']);
        Route::post('/book/{book}', [BookingController::class, 'cancel']);
        Route::get('/own-bookings', [BookingController::class, 'showOwnBooking']);
        Route::get('/own-booking-logs', [BookingLogController::class, 'showOwnBookingLog']);
    });
});

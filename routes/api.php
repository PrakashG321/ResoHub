<?php

use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\BookingLogController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\ResourceTypeController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

Route::get('/auth/verify-email/{id}/{hash}', function (Request $request, $id, $hash) {
    $user = User::find($id);

    if (!$user) {
        return response()->json(['message' => 'User not found.'], 404);
    }

    // Validate the hash matches the personal email hash
    if (!hash_equals((string) $hash, sha1($user->personal_email))) {
        return response()->json(['message' => 'Invalid or expired verification link.'], 403);
    }

    if ($user->hasVerifiedEmail()) {
        return response()->json(['message' => 'Email already verified.']);
    }

    // Instead of verifying now, just confirm the link is valid
    return response()->json([
        'message' => 'Verification link valid. Please set your password.',
        'user_id' => $user->id,
        'email' => $user->email,  // or personal_email if you want
    ]);
})->middleware('signed')->name('verification.verify');


Route::post('/auth/set-password', function (Request $request) {
    $request->validate([
        'user_id' => 'required|exists:users,id',
        'password' => 'required|string|min:8|confirmed',
        'hash' => 'required|string',
    ]);

    $user = User::findOrFail($request->user_id);

    // Validate hash again for security
    if (!hash_equals($request->hash, sha1($user->personal_email))) {
        return response()->json(['message' => 'Invalid or expired verification link.'], 403);
    }

    if ($user->password) {
        return response()->json(['message' => 'Password already set.'], 400);
    }

    // Save hashed password
    $user->password = bcrypt($request->password);
    $user->markEmailAsVerified();
    $user->save();

    return response()->json(['message' => 'Password set and email verified successfully. You can now log in.']);
});



Route::post("/login", [AuthenticationController::class, 'login'])->name('login');
Route::middleware(['auth:api', 'verified'])->group(function () {
    Route::post("/logout", [AuthenticationController::class, 'logout']);
    Route::get('/bookings/by-date', [BookingController::class, 'getBookingsByDate']);

    Route::middleware('role:admin')->group(function () {
        Route::post('/add', [AuthenticationController::class, 'addMember']);
        Route::apiResource('resource', ResourceController::class);
        Route::apiResource('resource-types', ResourceTypeController::class);
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

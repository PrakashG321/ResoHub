<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookingLogRequest;
use App\Http\Requests\UpdateBookingLogRequest;
use App\Models\Booking;
use App\Models\BookingLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class BookingLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            $bookingLog = BookingLog::latest()->get();

            if ($bookingLog->isEmpty()) {
                return response()->json([
                    "message" => "no bookinglogs found"
                ], 200);
            }
            return response()->json([
                "bookingLog" => $bookingLog
            ], 200);
        } catch (\Exception $error) {
            return response()->json([
                "error" => $error->getMessage()
            ], 500);
        }
    }

    public function showOwnBookingLog(): JsonResponse
    {
        try {
            $bookingIds = Booking::where('user_id', Auth::id())->pluck('id');
            $bookingLogs = BookingLog::whereIn('booking_id', $bookingIds)->get();
            return response()->json([
                'bookingLogs' => $bookingLogs
            ], 200);
        } catch (\Exception $error) {
            return response()->json([
                "error" => $error->getMessage()
            ]);
        }
    }
   
}

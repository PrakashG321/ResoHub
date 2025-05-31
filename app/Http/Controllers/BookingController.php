<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\UpdateBookingRequest;
use App\Models\Booking;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            $bookings = Booking::latest()->get();

            if ($bookings->isEmpty()) {
                return response()->json([
                    "message" => "No bookings found"
                ], 204);
            }
            return response()->json([
                "booking" => $bookings
            ]);
        } catch (\Exception $error) {
            return response()->json([
                "error" => $error->getMessage()
            ], 400);
        }
    }

    public function showOwnBooking(): JsonResponse
    {
        try {
            $bookings = Booking::where('user_id', Auth::id())->get();

            if ($bookings->isEmpty()) {
                return response()->json([
                    'message' => 'You dont have any bookings'
                ], 200);
            }
            return response()->json([
                'bookings' => $bookings
            ], 200);
        } catch (\Exception $error) {
            return response()->json([
                "error" => $error->getMessage()
            ], 400);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBookingRequest $request): JsonResponse
    {
        try {
            $attributes = $request->validated();
            $booking = Booking::create($attributes);
            return response()->json([
                "message" => "Resource booked successfully",
                "booking" => $booking
            ], 201);
        } catch (\Exception $error) {
            return response()->json([
                "error" => $error->getMessage()
            ], 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Booking $booking): JsonResponse
    {
        try {
            if ($booking->isEmpty()) {
                return response()->json([
                    "message" => ""
                ], 200);
            }
            return response()->json([
                "booking" => $booking
            ], 200);
        } catch (\Exception $error) {
            return response()->json([
                "error" => $error->getMessage()
            ], 400);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBookingRequest $request, Booking $booking)
    {
        try {
            $attributes = $request->validated();
            $booking->update($attributes);
            return response()->json([
                "message" => "booking updated successfully",
                "booking" => $booking
            ], 200);
        } catch (\Exception $error) {
            return response()->json([
                "error" => $error->getMessage()
            ], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Booking $booking): JsonResponse
    {
        try {
            $booking->delete();
            return response()->json([
                "message" => "you have cancelled your booking"
            ], 200);
        } catch (\Exception $error) {
            return response()->json([
                "error" => $error->getMessage()
            ], 400);
        }
    }
}

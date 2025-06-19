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
            $user = Auth::user();

            $bookingLogsQuery = BookingLog::with('booking.resource.resource_type')->latest();

            $roleResourceMap = [
                 'computer_lab_supervisor' => 'Lab',
                'library_supervisor' => 'Library',
                'venue_hall_supervisor' => 'Venue',
                'sports_equipment_supervisor' => 'Sports',
            ];

            // Filter only if not admin
            if (!$user->hasRole('admin')) {
                $resourceTypeName = null;

                foreach ($roleResourceMap as $role => $resourceType) {
                    if ($user->hasRole($role)) {
                        $resourceTypeName = $resourceType;
                        break;
                    }
                }

                // Apply filter if a matching resource type was found
                if ($resourceTypeName) {
                    $bookingLogsQuery->whereHas('booking.resource.resource_type', function ($query) use ($resourceTypeName) {
                        $query->where('name', $resourceTypeName);
                    });
                }
            }

            $paginatedLogs = $bookingLogsQuery->paginate(10);

            if ($paginatedLogs->isEmpty()) {
                return response()->json([
                    "message" => "No booking logs found"
                ], 200);
            }

            return response()->json([
                "bookingLog" => $paginatedLogs
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
            $bookingLogs = BookingLog::whereIn('booking_id', $bookingIds)->paginate(10);
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

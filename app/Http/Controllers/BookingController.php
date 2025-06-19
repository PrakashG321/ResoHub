<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookingLogRequest;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\StoreDateRequest;
use App\Http\Requests\UpdateBookingRequest;
use App\Models\Booking;
use App\Models\BookingLog;
use App\Services\BookingService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class BookingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            $user = Auth::user(); // Get currently authenticated user

            // Define role → resource type mapping
            $roleResourceMap = [
                'computer_lab_supervisor' => 'Lab',
                'library_supervisor' => 'Library',
                'venue_hall_supervisor' => 'Venue',
                'sports_equipment_supervisor' => 'Sports',
            ];

            // Default: no filtering for admin
            $resourceTypeName = null;

            // If not admin, try to get their resource type
            if (!$user->hasRole('admin')) {
                foreach ($roleResourceMap as $role => $resourceType) {
                    if ($user->hasRole($role)) {
                        $resourceTypeName = $resourceType;
                        break;
                    }
                }
            }

            // Base query with eager loads
            $query = Booking::with(['user', 'resource.resource_type'])->latest();

            // Apply filter if resource type was detected
            if ($resourceTypeName) {
                $query->whereHas('resource.resource_type', function ($q) use ($resourceTypeName) {
                    $q->where('name', $resourceTypeName);
                });
            }

            // Paginate filtered result
            $bookings = $query->paginate(10);

            if ($bookings->isEmpty()) {
                return response()->json([
                    "message" => "No bookings found"
                ], 200);
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
            $bookings = Booking::with("resource")->where('user_id', Auth::id())->paginate(10);

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

    public function getBookingsByDate(StoreDateRequest $request): JsonResponse
    {
        try {
            $date = $request->validated()['date'];

            if (!$date) {
                return response()->json(['error' => 'Date parameter is required'], 400);
            }

            $targetDate = Carbon::parse($date)->startOfDay();
            $nextDate = Carbon::parse($date)->endOfDay();

            $bookings = Booking::where(function ($query) use ($targetDate, $nextDate) {
                $query->where('start_time', '<=', $nextDate)
                    ->where('end_time', '>=', $targetDate);
            })
                ->with(['user', 'resource'])
                ->get();

            return response()->json($bookings);
        } catch (\Exception $error) {
            return response()->json([
                'error' => $error->getMessage()
            ], 400);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBookingRequest $request, BookingService $bookingService, StoreBookingLogRequest $bookingLogRequest): JsonResponse
    {
        try {
            $attributes = $request->validated();
            $attributes['user_id'] = Auth::id();
            $bookingLogAttributes = $bookingLogRequest->validated();
            $result = $bookingService->createBooking($attributes, $bookingLogAttributes);
            if (isset($result['error'])) {
                return response()->json([
                    'error' => $result['error']
                ], $result['code'] ?? 400);
            }

            return response()->json([
                "message" => "Resource booked successfully",
                "booking" => $result['book']
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
    public function show(Booking $book): JsonResponse
    {
        try {
            if ($book->user_id != Auth::id()) {
                return response()->json([
                    'error' => 'Unauthorized access to this booking.'
                ], 403);
            }
            if ($book->status === 'cancelled') {
                return response()->json([
                    'error' => 'This booking has been cancelled.'
                ], 404);
            }
            return response()->json([
                'booking' => $book
            ], 200);
        } catch (\Exception $error) {
            return response()->json([
                'error' => $error->getMessage()
            ], 400);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBookingRequest $request, BookingService $bookingService, StoreBookingLogRequest $bookingLogRequest, Booking $book)
    {
        try {
            $attributes = $request->validated();
            $attributes["user_id"] = Auth::id();
            $bookingLogAttributes = $bookingLogRequest->validated();


            $result = $bookingService->updateBooking($attributes, $bookingLogAttributes, $book);
            if (isset($result['error'])) {
                return response()->json([
                    'error' => $result['error']
                ], $result['code'] ?? 400);
            }

            return response()->json([
                "message" => "Resource booked successfully",
                "booking" => $result['book']
            ], 201);
        } catch (\Exception $error) {
            return response()->json([
                "error" => $error->getMessage()
            ], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function cancel(Booking $book, StoreBookingLogRequest $request): JsonResponse
    {
        try {
            $book->update([
                'status' => 'cancelled'
            ]);

            $bookinglogAttributes = $request->validated();
            $bookinglogAttributes['changed_by'] = Auth::id();
            $bookinglogAttributes['booking_id'] = $book->id;
            $bookinglogAttributes['status'] = $book->status;

            BookingLog::create($bookinglogAttributes);

            return response()->json([
                'message' => 'Booking cancelled'
            ], 200);
        } catch (\Exception $error) {
            return response()->json([
                'error' => $error->getMessage()
            ], 400);
        }
    }


    public function approve(Booking $book, StoreBookingLogRequest $request): JsonResponse
    {
        try {

            Gate::authorize('approve', $book);

            if ($book->status !== 'pending') {
                return response()->json([
                    'error' => 'Booking cannot be approved as it is not in pending status.'
                ], 400);
            }

            $book->update([
                'status' => 'approved'
            ]);

            $bookinglogAttributes = $request->validated();
            $bookinglogAttributes['changed_by'] = Auth::id();
            $bookinglogAttributes['booking_id'] = $book->id;
            $bookinglogAttributes['status'] = $book->status;

            BookingLog::create($bookinglogAttributes);

            return response()->json([
                'message' => 'Booking approved'
            ], 200);
        } catch (\Exception $error) {
            return response()->json([
                'error' => $error->getMessage()
            ], 400);
        }
    }

    public function reject(Booking $book, StoreBookingLogRequest $request): JsonResponse
    {
        try {

            Gate::authorize('reject', $book);
            if ($book->status != 'pending') {
                return response()->json([
                    'error' => 'Booking cannot be approved as it is not in pending status.'
                ], 400);
            }

            $book->update([
                'status' => 'rejected'
            ]);

            $bookinglogAttributes = $request->validated();
            $bookinglogAttributes['changed_by'] = Auth::id();
            $bookinglogAttributes['booking_id'] = $book->id;
            $bookinglogAttributes['status'] = $book->status;


            BookingLog::create($bookinglogAttributes);
            return response()->json([
                'message' => 'Booking approved'
            ], 200);
        } catch (\Exception $error) {
            return response()->json([
                'error' => $error->getMessage()
            ], 400);
        }
    }
}

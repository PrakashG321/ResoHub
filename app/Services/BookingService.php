<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Resource;
use App\Models\BookingLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class BookingService
{
    public function createBooking(array $attributes, array $bookingLogAttributes): array
    {
        $presentTime = Carbon::now();
        $startTime = Carbon::parse(time: $attributes['start_time']);
        $endTime = Carbon::parse($attributes['end_time']);


        if ($startTime->lessThan($presentTime)) {
            return ['error' => 'Booking start time cannot be in the past.', 'code' => 400];
        }

        try {
            //db transaction to ensure all process happens at once, if one fails then no commitment will be made
            DB::beginTransaction();

            $resource = Resource::where('id', $attributes['resource_id'])->lockForUpdate()->first();

            if ($resource && $resource->status === 'undermaintenance') {
                DB::rollBack();
                return ["error" => "Resource is under maintenance and cannot be booked.", 'code' => 400];
            }

            $overlappingBookings = Booking::where('resource_id', $attributes['resource_id'])
                ->whereIn('status', ['approved', 'booked', 'pending'])
                ->where(function ($query) use ($startTime, $endTime) {
                    $query->whereBetween('start_time', [$startTime, $endTime])
                        ->orWhereBetween('end_time', [$startTime, $endTime])
                        ->orWhere(function ($query) use ($startTime, $endTime) {
                            $query->where('start_time', '<=', $startTime)
                                ->where('end_time', '>=', $endTime);
                        });
                })
                ->get();

            if ($overlappingBookings->isNotEmpty()) {
                if ($overlappingBookings->where('status', 'pending')->isNotEmpty()) {
                    DB::rollBack();
                    return ["error" => "Resource already has a pending booking request during the selected time.", "code" => 400];
                }
                // If no pending bookings, but approved/booked exists:
                DB::rollBack();
                return ["error" => "Resource is already booked during the requested time.", "code" => 400];
            }

            $attributes['status'] = 'pending';
            $book = Booking::create($attributes);

            $bookingLogAttributes['changed_by'] = Auth::id();
            $bookingLogAttributes['booking_id'] = $book->id;
            $bookingLogAttributes['status'] = $book->status;

            BookingLog::create($bookingLogAttributes);

            //begin tracsaction keeps the database queries at pending state and this commit is called the transaction actually happens
            DB::commit();

            return ['book' => $book];
        } catch (\Exception $exception) {
            DB::rollBack();
            return [
                'error' => $exception->getMessage(),
                'code' => 500,
            ];
        }
    }

    public function updateBooking(array $attributes, array $bookingLogAttributes, Booking $book): array
    {
        $presentTime = Carbon::now();
        $startTime = Carbon::parse(time: $attributes['start_time']);
        $endTime = Carbon::parse($attributes['end_time']);


        if ($startTime->lessThan($presentTime)) {
            return ['error' => 'Booking start time cannot be in the past.', 'code' => 400];
        }

        try {
            //db transaction to ensure all process happens at once, if one fails then no commitment will be made
            DB::beginTransaction();

            $resourceId = $attributes['resource_id'] ?? $book->resource_id;
            $resource = Resource::where('id', $resourceId)->lockForUpdate()->first();

            if ($resource && $resource->status === 'undermaintenance') {
                return ["error" => "Resource is under maintenance and cannot be booked.", 'code' => 400];
            }

            $overlappingBookings = Booking::where('resource_id', $resourceId)
                ->whereIn('status', ['approved', 'booked', 'pending'])
                ->where(function ($query) use ($startTime, $endTime) {
                    $query->whereBetween('start_time', [$startTime, $endTime])
                        ->orWhereBetween('end_time', [$startTime, $endTime])
                        ->orWhere(function ($query) use ($startTime, $endTime) {
                            $query->where('start_time', '<=', $startTime)
                                ->where('end_time', '>=', $endTime);
                        });
                })
                ->get();

            if ($overlappingBookings->isNotEmpty()) {
                if ($overlappingBookings->where('status', 'pending')->isNotEmpty()) {
                    DB::rollBack();
                    return ["error" => "Resource already has a pending booking request during the selected time.", "code" => 400];
                }
                // If no pending bookings, but approved/booked exists:
                DB::rollBack();
                return ["error" => "Resource is already booked during the requested time.", "code" => 400];
            }

            $book->update($attributes);

            $bookingLogAttributes['changed_by'] = Auth::id();
            $bookingLogAttributes['booking_id'] = $book->id;
            $bookingLogAttributes['status'] = $book->status;

           

            BookingLog::create($bookingLogAttributes);

            //begin tracsaction keeps the database queries at pending state and this commit is called the transaction actually happens
            DB::commit();

            return ['book' => $book];
        } catch (\Exception $exception) {
            DB::rollBack();
            return [
                'error' => $exception->getMessage(),
                'code' => 500,
            ];
        }
    }
}

<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\Resource;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateResourceStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-resource-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();

        $ongoingBookings = Booking::where('status', 'approved')
            ->where('start_time', '<=', $now)
            ->where('end_time', '>', $now)
            ->get();

        foreach ($ongoingBookings as $booking) {
            Resource::where('id', $booking->resource_id)
                ->where('status', '!=', 'booked')
                ->update(['status' => 'booked']);
        }

        $expiredBookings = Booking::where('status', 'approved')
            ->where('end_time', '<=', $now)
            ->get();

        foreach ($expiredBookings as $booking) {
            Resource::where('id', $booking->resource_id)
                ->where('status', '!=', 'available')
                ->update(['status' => 'available']);
            $booking->update(['status' => 'completed']);
        }

        $this->info('Resource statuses updated successfully.');
    }
}

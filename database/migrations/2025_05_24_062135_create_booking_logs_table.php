<?php

use App\Models\Booking;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('booking_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class, 'changed_by')->constrained()->onDelete('cascade');
            $table->foreignIdFor(Booking::class)->constrained()->onDelete('cascade');
            $table->text('comment')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->timestamps();   
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_logs');
    }
};

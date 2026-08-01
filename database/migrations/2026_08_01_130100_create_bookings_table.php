<?php

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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guest_id')->constrained('guests')->restrictOnDelete();
            $table->foreignId('room_id')->nullable()->constrained('rooms')->nullOnDelete();
            $table->string('booking_reference', 40)->unique();
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->string('status', 30)->default('reserved');
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->timestamps();

            $table->index(['guest_id', 'check_in_date']);
            $table->index(['guest_id', 'check_out_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};

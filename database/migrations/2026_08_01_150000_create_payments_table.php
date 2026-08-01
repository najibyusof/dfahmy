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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->string('receipt_number', 60)->unique();
            $table->date('payment_date');
            $table->decimal('amount', 10, 2);
            $table->string('payment_method', 30);
            $table->string('reference_number', 100)->nullable();
            $table->string('payment_status', 20)->default('pending');
            $table->foreignId('received_by_user_id')->constrained('users')->restrictOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['booking_id', 'payment_date']);
            $table->index(['payment_status', 'payment_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};

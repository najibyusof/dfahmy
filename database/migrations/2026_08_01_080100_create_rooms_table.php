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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('building_id')->constrained('buildings')->restrictOnDelete();
            $table->string('name');
            $table->string('code', 30)->unique();
            $table->unsignedTinyInteger('floor')->default(1);
            $table->string('room_type', 50);
            $table->string('status', 30)->default('available');
            $table->decimal('base_nightly_rate', 10, 2)->default(0);
            $table->unsignedTinyInteger('maximum_guests')->default(1);
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['building_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};

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
        Schema::create('bookable_units', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('code', 60)->unique();
            $table->text('description')->nullable();
            $table->string('booking_type', 30);
            $table->decimal('base_nightly_rate', 10, 2)->default(0);
            $table->unsignedInteger('maximum_guests')->default(1);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['booking_type', 'is_active']);
            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('bookable_unit_room', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bookable_unit_id')->constrained('bookable_units')->cascadeOnDelete();
            $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['bookable_unit_id', 'room_id']);
            $table->index(['room_id', 'bookable_unit_id']);
        });

        Schema::table('booking_room_items', function (Blueprint $table) {
            $table->foreignId('bookable_unit_id')->nullable()->after('booking_id')->constrained('bookable_units')->nullOnDelete();
            $table->string('bookable_unit_name', 120)->nullable()->after('bookable_unit_id');
            $table->string('bookable_unit_code', 60)->nullable()->after('bookable_unit_name');
            $table->string('booking_type', 30)->nullable()->after('bookable_unit_code');
            $table->json('included_rooms_snapshot')->nullable()->after('booking_type');

            $table->index(['bookable_unit_id', 'check_in_date', 'check_out_date'], 'bri_bookable_unit_dates_idx');
        });

        Schema::create('booking_room_item_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_room_item_id')->constrained('booking_room_items')->cascadeOnDelete();
            $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['booking_room_item_id', 'room_id'], 'bri_room_unique_idx');
            $table->index(['room_id', 'booking_room_item_id'], 'bri_room_lookup_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_room_item_rooms');

        Schema::table('booking_room_items', function (Blueprint $table) {
            $table->dropIndex('bri_bookable_unit_dates_idx');
            $table->dropConstrainedForeignId('bookable_unit_id');
            $table->dropColumn([
                'bookable_unit_name',
                'bookable_unit_code',
                'booking_type',
                'included_rooms_snapshot',
            ]);
        });

        Schema::dropIfExists('bookable_unit_room');
        Schema::dropIfExists('bookable_units');
    }
};

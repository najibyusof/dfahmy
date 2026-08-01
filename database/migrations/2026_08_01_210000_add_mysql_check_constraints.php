<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE bookings ADD CONSTRAINT chk_bookings_total_amount_non_negative CHECK (total_amount >= 0)");
        DB::statement("ALTER TABLE bookings ADD CONSTRAINT chk_bookings_date_range CHECK (check_out_date > check_in_date)");

        DB::statement("ALTER TABLE booking_room_items ADD CONSTRAINT chk_booking_room_items_date_range CHECK (check_out_date > check_in_date)");
        DB::statement("ALTER TABLE booking_room_items ADD CONSTRAINT chk_booking_room_items_nightly_rate_non_negative CHECK (nightly_rate >= 0)");
        DB::statement("ALTER TABLE booking_room_items ADD CONSTRAINT chk_booking_room_items_adults_positive CHECK (adults >= 1)");
        DB::statement("ALTER TABLE booking_room_items ADD CONSTRAINT chk_booking_room_items_children_non_negative CHECK (children >= 0)");

        DB::statement("ALTER TABLE payments ADD CONSTRAINT chk_payments_amount_positive CHECK (amount > 0)");

        DB::statement("ALTER TABLE rooms ADD CONSTRAINT chk_rooms_base_nightly_rate_non_negative CHECK (base_nightly_rate >= 0)");
        DB::statement("ALTER TABLE rooms ADD CONSTRAINT chk_rooms_maximum_guests_positive CHECK (maximum_guests >= 1)");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE rooms DROP CHECK chk_rooms_maximum_guests_positive');
        DB::statement('ALTER TABLE rooms DROP CHECK chk_rooms_base_nightly_rate_non_negative');

        DB::statement('ALTER TABLE payments DROP CHECK chk_payments_amount_positive');

        DB::statement('ALTER TABLE booking_room_items DROP CHECK chk_booking_room_items_children_non_negative');
        DB::statement('ALTER TABLE booking_room_items DROP CHECK chk_booking_room_items_adults_positive');
        DB::statement('ALTER TABLE booking_room_items DROP CHECK chk_booking_room_items_nightly_rate_non_negative');
        DB::statement('ALTER TABLE booking_room_items DROP CHECK chk_booking_room_items_date_range');

        DB::statement('ALTER TABLE bookings DROP CHECK chk_bookings_date_range');
        DB::statement('ALTER TABLE bookings DROP CHECK chk_bookings_total_amount_non_negative');
    }
};

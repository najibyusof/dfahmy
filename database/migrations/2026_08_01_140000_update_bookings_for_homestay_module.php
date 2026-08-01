<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->unsignedTinyInteger('adults')->default(1)->after('check_out_date');
            $table->unsignedTinyInteger('children')->default(0)->after('adults');
            $table->string('booking_source', 50)->default('other')->after('children');
            $table->string('booking_status', 30)->default('pending')->after('booking_source');
            $table->text('special_requests')->nullable()->after('booking_status');
            $table->text('internal_notes')->nullable()->after('special_requests');
            $table->decimal('subtotal', 10, 2)->default(0)->after('internal_notes');
            $table->decimal('discount', 10, 2)->default(0)->after('subtotal');
            $table->decimal('tax', 10, 2)->default(0)->after('discount');

            $table->index(['booking_status', 'check_in_date']);
        });

        DB::table('bookings')->update([
            'booking_status' => DB::raw("CASE
                WHEN status = 'reserved' THEN 'confirmed'
                WHEN status = 'checked_in' THEN 'checked_in'
                WHEN status = 'checked_out' THEN 'checked_out'
                WHEN status = 'cancelled' THEN 'cancelled'
                ELSE 'pending'
            END"),
            'subtotal' => DB::raw('total_amount'),
        ]);

        Schema::create('booking_room_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('room_id')->constrained('rooms')->restrictOnDelete();
            $table->decimal('nightly_rate', 10, 2)->default(0);
            $table->unsignedTinyInteger('adults')->default(1);
            $table->unsignedTinyInteger('children')->default(0);
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->timestamps();

            $table->index(['room_id', 'check_in_date', 'check_out_date']);
        });

        DB::table('bookings')
            ->whereNotNull('room_id')
            ->orderBy('id')
            ->chunkById(100, function ($bookings): void {
                $rows = [];

                foreach ($bookings as $booking) {
                    $rows[] = [
                        'booking_id' => $booking->id,
                        'room_id' => $booking->room_id,
                        'nightly_rate' => (float) $booking->total_amount,
                        'adults' => max(1, (int) $booking->adults),
                        'children' => max(0, (int) $booking->children),
                        'check_in_date' => $booking->check_in_date,
                        'check_out_date' => $booking->check_out_date,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                if ($rows !== []) {
                    DB::table('booking_room_items')->insert($rows);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_room_items');

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex(['booking_status', 'check_in_date']);
            $table->dropColumn([
                'adults',
                'children',
                'booking_source',
                'booking_status',
                'special_requests',
                'internal_notes',
                'subtotal',
                'discount',
                'tax',
            ]);
        });
    }
};

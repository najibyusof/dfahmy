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
        DB::table('booking_room_items')
            ->select(['id', 'room_id'])
            ->whereNotNull('room_id')
            ->orderBy('id')
            ->chunkById(500, function ($items): void {
                $rows = [];

                foreach ($items as $item) {
                    $rows[] = [
                        'booking_room_item_id' => $item->id,
                        'room_id' => $item->room_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                if ($rows !== []) {
                    DB::table('booking_room_item_rooms')->upsert(
                        $rows,
                        ['booking_room_item_id', 'room_id'],
                        ['updated_at']
                    );
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('booking_room_item_rooms')->truncate();
    }
};

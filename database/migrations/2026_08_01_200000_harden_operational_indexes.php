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
        Schema::table('bookings', function (Blueprint $table): void {
            $table->index(['check_in_date', 'check_out_date'], 'bookings_checkin_checkout_idx');
            $table->index(['booking_source', 'booking_status'], 'bookings_source_status_idx');
        });

        Schema::table('booking_room_items', function (Blueprint $table): void {
            $table->index(['booking_id', 'room_id'], 'booking_room_items_booking_room_idx');
            $table->unique(
                ['booking_id', 'room_id', 'check_in_date', 'check_out_date'],
                'booking_room_items_unique_line_idx'
            );
        });

        Schema::table('payments', function (Blueprint $table): void {
            $table->index(['booking_id', 'payment_status'], 'payments_booking_status_idx');
            $table->index(['payment_method', 'payment_date'], 'payments_method_date_idx');
        });

        Schema::table('housekeeping_tasks', function (Blueprint $table): void {
            $table->index(['booking_id', 'status'], 'housekeeping_tasks_booking_status_idx');
            $table->index(['task_type', 'priority', 'due_date'], 'housekeeping_tasks_type_priority_due_idx');
        });

        Schema::table('audit_logs', function (Blueprint $table): void {
            $table->index(['user_id', 'created_at'], 'audit_logs_user_created_idx');
            $table->index(['subject_type', 'action', 'created_at'], 'audit_logs_subject_action_created_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table): void {
            $table->dropIndex('audit_logs_subject_action_created_idx');
            $table->dropIndex('audit_logs_user_created_idx');
        });

        Schema::table('housekeeping_tasks', function (Blueprint $table): void {
            $table->dropIndex('housekeeping_tasks_type_priority_due_idx');
            $table->dropIndex('housekeeping_tasks_booking_status_idx');
        });

        Schema::table('payments', function (Blueprint $table): void {
            $table->dropIndex('payments_method_date_idx');
            $table->dropIndex('payments_booking_status_idx');
        });

        Schema::table('booking_room_items', function (Blueprint $table): void {
            $table->dropUnique('booking_room_items_unique_line_idx');
            $table->dropIndex('booking_room_items_booking_room_idx');
        });

        Schema::table('bookings', function (Blueprint $table): void {
            $table->dropIndex('bookings_source_status_idx');
            $table->dropIndex('bookings_checkin_checkout_idx');
        });
    }
};

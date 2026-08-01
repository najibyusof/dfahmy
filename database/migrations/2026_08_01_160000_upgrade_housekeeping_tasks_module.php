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
        Schema::table('housekeeping_tasks', function (Blueprint $table) {
            $table->foreignId('room_id')->nullable()->after('assigned_to_user_id')->constrained('rooms')->nullOnDelete();
            $table->foreignId('booking_id')->nullable()->after('room_id')->constrained('bookings')->nullOnDelete();
            $table->string('task_type', 40)->default('other')->after('room_label');
            $table->string('priority', 20)->default('medium')->after('task_type');
            $table->date('due_date')->nullable()->after('priority');
            $table->text('checklist_notes')->nullable()->after('notes');
            $table->timestamp('completed_at')->nullable()->after('checklist_notes');

            $table->index(['status', 'due_date']);
            $table->index(['assigned_to_user_id', 'status']);
            $table->index(['room_id', 'task_type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('housekeeping_tasks', function (Blueprint $table) {
            $table->dropIndex(['status', 'due_date']);
            $table->dropIndex(['assigned_to_user_id', 'status']);
            $table->dropIndex(['room_id', 'task_type', 'status']);

            $table->dropConstrainedForeignId('booking_id');
            $table->dropConstrainedForeignId('room_id');
            $table->dropColumn([
                'task_type',
                'priority',
                'due_date',
                'checklist_notes',
                'completed_at',
            ]);
        });
    }
};

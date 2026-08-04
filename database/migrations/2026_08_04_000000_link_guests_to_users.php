<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guests', function (Blueprint $table): void {
            $table->foreignId('user_id')->nullable()->unique()->after('id')->constrained()->nullOnDelete();
            $table->string('phone_number', 30)->nullable()->change();
            $table->string('identification_number', 100)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('guests', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('user_id');
            $table->string('phone_number', 30)->nullable(false)->change();
            $table->string('identification_number', 100)->nullable(false)->change();
        });
    }
};

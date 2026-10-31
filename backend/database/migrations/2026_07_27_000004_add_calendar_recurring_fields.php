<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calendar_events', function (Blueprint $table) {
            $table->enum('repeat_interval', ['none', 'daily', 'monthly', 'yearly'])->default('none')->after('is_official');
            $table->uuid('repeat_group_id')->nullable()->after('repeat_interval');
        });
    }

    public function down(): void
    {
        Schema::table('calendar_events', function (Blueprint $table) {
            $table->dropColumn(['repeat_interval', 'repeat_group_id']);
        });
    }
};

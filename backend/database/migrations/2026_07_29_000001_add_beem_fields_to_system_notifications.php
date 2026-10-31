<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('system_notifications', function (Blueprint $table) {
            if (! Schema::hasColumn('system_notifications', 'channel')) {
                $table->enum('channel', ['in_app', 'sms', 'email'])->default('in_app')->after('level');
            }
            if (! Schema::hasColumn('system_notifications', 'recipients')) {
                $table->json('recipients')->nullable()->after('channel');
            }
            if (! Schema::hasColumn('system_notifications', 'sent_at')) {
                $table->timestamp('sent_at')->nullable()->after('recipients');
            }
        });
    }

    public function down(): void
    {
        Schema::table('system_notifications', function (Blueprint $table) {
            if (Schema::hasColumn('system_notifications', 'sent_at')) {
                $table->dropColumn('sent_at');
            }
            if (Schema::hasColumn('system_notifications', 'recipients')) {
                $table->dropColumn('recipients');
            }
            if (Schema::hasColumn('system_notifications', 'channel')) {
                $table->dropColumn('channel');
            }
        });
    }
};

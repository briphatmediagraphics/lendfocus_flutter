<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_ticket_replies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('support_ticket_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->enum('sender_type', ['customer', 'admin'])->default('customer');
            $table->text('message');
            $table->boolean('is_internal')->default(false);
            $table->timestamps();

            $table->foreign('support_ticket_id')->references('id')->on('support_tickets')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('system_notifications', function (Blueprint $table) {
            if (!Schema::hasColumn('system_notifications', 'channel')) {
                $table->string('channel')->default('in_app')->after('body');
            }

            if (!Schema::hasColumn('system_notifications', 'recipients')) {
                $table->json('recipients')->nullable()->after('channel');
            }

            if (!Schema::hasColumn('system_notifications', 'sent_at')) {
                $table->timestamp('sent_at')->nullable()->after('recipients');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_ticket_replies');

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

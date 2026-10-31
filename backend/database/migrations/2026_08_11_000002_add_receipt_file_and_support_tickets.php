<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $table) {
                if (! Schema::hasColumn('payments', 'receipt_file')) {
                    $table->string('receipt_file')->nullable()->after('reference');
                }
            });
        }

        if (! Schema::hasTable('support_tickets')) {
            Schema::create('support_tickets', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('ticket_number')->unique();
                $table->uuid('user_id')->nullable();
                $table->uuid('project_id')->nullable();
                $table->string('subject');
                $table->text('message');
                $table->enum('status', ['open', 'pending', 'resolved', 'closed'])->default('open');
                $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
                $table->uuid('assigned_to')->nullable();
                $table->timestamp('last_replied_at')->nullable();
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
                $table->foreign('project_id')->references('id')->on('projects')->nullOnDelete();
                $table->foreign('assigned_to')->references('id')->on('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('support_tickets')) {
            Schema::dropIfExists('support_tickets');
        }

        if (Schema::hasTable('payments') && Schema::hasColumn('payments', 'receipt_file')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->dropColumn('receipt_file');
            });
        }
    }
};

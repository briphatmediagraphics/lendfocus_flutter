<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('support_tickets')) {
            Schema::create('support_tickets', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('ticket_number')->unique();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->uuid('project_id')->nullable();
                $table->string('customer_name')->nullable();
                $table->string('customer_email')->nullable();
                $table->string('customer_phone')->nullable();
                $table->string('subject');
                $table->text('message');
                $table->enum('status', ['open', 'pending', 'resolved', 'closed'])->default('open');
                $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
                $table->unsignedBigInteger('assigned_to')->nullable();
                $table->timestamp('last_replied_at')->nullable();
                $table->text('admin_notes')->nullable();
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
                $table->foreign('assigned_to')->references('id')->on('users')->nullOnDelete();
            });
        }

        Schema::table('support_tickets', function (Blueprint $table) {
            if (! Schema::hasColumn('support_tickets', 'customer_name')) {
                $table->string('customer_name')->nullable()->after('user_id');
            }

            if (! Schema::hasColumn('support_tickets', 'customer_email')) {
                $table->string('customer_email')->nullable()->after('customer_name');
            }

            if (! Schema::hasColumn('support_tickets', 'customer_phone')) {
                $table->string('customer_phone')->nullable()->after('customer_email');
            }

            if (! Schema::hasColumn('support_tickets', 'admin_notes')) {
                $table->text('admin_notes')->nullable()->after('last_replied_at');
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('support_tickets')) {
            Schema::table('support_tickets', function (Blueprint $table) {
                $table->dropColumn(['customer_name', 'customer_email', 'customer_phone', 'admin_notes']);
            });
        }
    }
};

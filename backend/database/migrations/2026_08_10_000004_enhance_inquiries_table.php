<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('inquiries')) {
            return;
        }

        Schema::table('inquiries', function (Blueprint $table) {
            if (! Schema::hasColumn('inquiries', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
            }

            if (! Schema::hasColumn('inquiries', 'ticket_number')) {
                $table->string('ticket_number')->unique()->nullable()->after('id');
            }

            if (
                Schema::hasColumn('inquiries', 'project_id')
                && DB::connection()->getDriverName() === 'mysql'
            ) {
                DB::statement('ALTER TABLE inquiries MODIFY project_id BIGINT UNSIGNED NULL');
            }

            if (! Schema::hasColumn('inquiries', 'priority')) {
                $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium')->after('status');
            }

            if (! Schema::hasColumn('inquiries', 'admin_notes')) {
                $table->text('admin_notes')->nullable()->after('notes');
            }

            if (! Schema::hasColumn('inquiries', 'last_replied_at')) {
                $table->timestamp('last_replied_at')->nullable()->after('updated_at');
            }

            if (! Schema::hasColumn('inquiries', 'assigned_to')) {
                $table->unsignedBigInteger('assigned_to')->nullable()->after('id');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('inquiries')) {
            return;
        }

        Schema::table('inquiries', function (Blueprint $table) {
            if (Schema::hasColumn('inquiries', 'user_id')) {
                $table->dropColumn('user_id');
            }

            if (Schema::hasColumn('inquiries', 'ticket_number')) {
                $table->dropColumn('ticket_number');
            }

            if (Schema::hasColumn('inquiries', 'priority')) {
                $table->dropColumn('priority');
            }

            if (Schema::hasColumn('inquiries', 'admin_notes')) {
                $table->dropColumn('admin_notes');
            }

            if (Schema::hasColumn('inquiries', 'last_replied_at')) {
                $table->dropColumn('last_replied_at');
            }

            if (Schema::hasColumn('inquiries', 'assigned_to')) {
                $table->dropColumn('assigned_to');
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->uuid('worker_id')->nullable()->after('assigned_to');
            $table->boolean('approved')->default(false)->after('completed');
            $table->timestamp('completed_at')->nullable()->after('approved');
            $table->timestamp('approved_at')->nullable()->after('completed_at');
            $table->string('approved_by')->nullable()->after('approved_at');

            $table->foreign('worker_id')->references('id')->on('workers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['worker_id']);
            $table->dropColumn(['worker_id', 'approved', 'completed_at', 'approved_at', 'approved_by']);
        });
    }
};

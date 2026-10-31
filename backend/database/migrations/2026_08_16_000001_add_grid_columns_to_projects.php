<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('projects') && ! Schema::hasColumn('projects', 'grid_columns')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->unsignedTinyInteger('grid_columns')->default(10)->after('map_layout_type');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('projects') && Schema::hasColumn('projects', 'grid_columns')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->dropColumn('grid_columns');
            });
        }
    }
};

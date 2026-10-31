<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('projects')) {
            Schema::table('projects', function (Blueprint $table) {
                if (! Schema::hasColumn('projects', 'cover_image')) {
                    $table->string('cover_image')->nullable()->after('cover_path');
                }
                if (! Schema::hasColumn('projects', 'total_plots')) {
                    $table->integer('total_plots')->default(0)->after('base_price_tzs');
                }
                if (! Schema::hasColumn('projects', 'map_layout_type')) {
                    $table->enum('map_layout_type', ['grid','coordinates'])->default('grid')->after('total_plots');
                }
            });
        }

        if (Schema::hasTable('plots')) {
            Schema::table('plots', function (Blueprint $table) {
                if (! Schema::hasColumn('plots', 'position_x')) {
                    $table->integer('position_x')->nullable()->after('status');
                }
                if (! Schema::hasColumn('plots', 'position_y')) {
                    $table->integer('position_y')->nullable()->after('position_x');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('projects')) {
            Schema::table('projects', function (Blueprint $table) {
                if (Schema::hasColumn('projects', 'map_layout_type')) {
                    $table->dropColumn('map_layout_type');
                }
                if (Schema::hasColumn('projects', 'total_plots')) {
                    $table->dropColumn('total_plots');
                }
                if (Schema::hasColumn('projects', 'cover_image')) {
                    $table->dropColumn('cover_image');
                }
            });
        }

        if (Schema::hasTable('plots')) {
            Schema::table('plots', function (Blueprint $table) {
                if (Schema::hasColumn('plots', 'position_y')) {
                    $table->dropColumn('position_y');
                }
                if (Schema::hasColumn('plots', 'position_x')) {
                    $table->dropColumn('position_x');
                }
            });
        }
    }
};

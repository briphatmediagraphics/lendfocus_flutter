<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('projects')) {
            Schema::table('projects', function (Blueprint $table) {
                if (! Schema::hasColumn('projects', 'total_plots')) {
                    $table->unsignedInteger('total_plots')->default(0)->after('description');
                }
                if (! Schema::hasColumn('projects', 'cover_image')) {
                    $table->string('cover_image')->nullable()->after('cover_path');
                }
                if (! Schema::hasColumn('projects', 'map_layout_type')) {
                    $table->enum('map_layout_type', ['grid', 'coordinates'])->default('grid')->after('cover_image');
                }
            });
        }

        if (Schema::hasTable('plots')) {
            Schema::table('plots', function (Blueprint $table) {
                if (! Schema::hasColumn('plots', 'position_x')) {
                    $table->unsignedInteger('position_x')->nullable()->after('status');
                }
                if (! Schema::hasColumn('plots', 'position_y')) {
                    $table->unsignedInteger('position_y')->nullable()->after('position_x');
                }
                if (! Schema::hasColumn('plots', 'plot_number')) {
                    $table->string('plot_number')->nullable()->after('project_id');
                }
            });

            try {
                DB::statement("ALTER TABLE plots MODIFY status ENUM('available','held','sold','reserved','open','booked','paid') NOT NULL DEFAULT 'available'");
            } catch (\Throwable $e) {
                // Some DBs do not support modifying enum via raw statement. Skip if unsupported.
            }

            try {
                $indexes = DB::select('SHOW INDEXES FROM plots');
                $hasUnique = collect($indexes)->contains(fn ($index) => ($index->Key_name ?? null) === 'plots_project_id_plot_number_unique');
                if (! $hasUnique) {
                    Schema::table('plots', function (Blueprint $table) {
                        $table->unique(['project_id', 'plot_number'], 'plots_project_id_plot_number_unique');
                    });
                }
            } catch (\Throwable $e) {
                // Ignore if schema introspection is unsupported for this DB.
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('plots')) {
            Schema::table('plots', function (Blueprint $table) {
                if (Schema::hasColumn('plots', 'plot_number')) {
                    $table->dropUnique('plots_project_id_plot_number_unique');
                    $table->dropColumn(['position_x', 'position_y', 'plot_number']);
                }
            });
        }

        if (Schema::hasTable('projects')) {
            Schema::table('projects', function (Blueprint $table) {
                if (Schema::hasColumn('projects', 'total_plots')) {
                    $table->dropColumn(['total_plots', 'cover_image', 'map_layout_type']);
                }
            });
        }
    }
};

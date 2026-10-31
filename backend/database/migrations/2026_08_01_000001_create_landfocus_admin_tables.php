<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('CREATE EXTENSION IF NOT EXISTS pgcrypto');
        }

        if (! Schema::hasTable('user_roles')) {
            Schema::create('user_roles', function (Blueprint $table) {
                $table->foreignId('user_id');
                $table->enum('role', ['superadmin', 'admin', 'sales_officer', 'accountant', 'field_officer']);
                $table->timestamps();
                $table->primary(['user_id', 'role']);
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('projects')) {
            Schema::create('projects', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('code')->unique();
                $table->string('title');
                $table->string('slug')->unique();
                $table->enum('type', ['shamba', 'ardhi', 'nyumba']);
                $table->string('location');
                $table->string('region')->nullable();
                $table->string('district')->nullable();
                $table->string('cover_path')->nullable();
                $table->text('description')->nullable();
                $table->json('geojson')->nullable();
                $table->enum('status', ['inapatikana', 'imefungwa', 'imekamilika'])->default('inapatikana');
                $table->bigInteger('base_price_tzs')->default(0);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            });
        }

        if (! Schema::hasTable('plots')) {
            Schema::create('plots', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('project_id');
                $table->string('number');
                $table->integer('size_sqm');
                $table->bigInteger('price_tzs');
                $table->enum('status', ['available', 'held', 'sold', 'reserved'])->default('available');
                $table->json('coordinates')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('project_id')->references('id')->on('projects')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('bookings')) {
            Schema::create('bookings', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('plot_id');
                $table->string('customer_name');
                $table->string('customer_phone');
                $table->string('customer_id_no');
                $table->string('control_number', 12)->unique();
                $table->bigInteger('price_tzs');
                $table->enum('status', ['held', 'pending_verification', 'confirmed', 'rejected', 'expired'])->default('held');
                $table->timestamp('held_until')->nullable();
                $table->unsignedBigInteger('sales_officer_id')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('plot_id')->references('id')->on('plots')->cascadeOnDelete();
                $table->foreign('sales_officer_id')->references('id')->on('users')->nullOnDelete();
            });
        }

        if (! Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('booking_id');
                $table->enum('method', ['mpesa', 'airtelmoney', 'tigopesa', 'halopesa', 'crdb', 'nmb', 'cash']);
                $table->string('reference')->nullable();
                $table->bigInteger('amount_tzs');
                $table->timestamp('paid_at')->nullable();
                $table->unsignedBigInteger('verified_by')->nullable();
                $table->timestamp('verified_at')->nullable();
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('booking_id')->references('id')->on('bookings')->cascadeOnDelete();
                $table->foreign('verified_by')->references('id')->on('users')->nullOnDelete();
            });
        }

        if (! Schema::hasTable('receipts')) {
            Schema::create('receipts', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('booking_id')->nullable();
                $table->uuid('payment_id')->nullable();
                $table->string('storage_path')->nullable();
                $table->unsignedBigInteger('uploaded_by');
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('booking_id')->references('id')->on('bookings')->cascadeOnDelete();
                $table->foreign('payment_id')->references('id')->on('payments')->cascadeOnDelete();
                $table->foreign('uploaded_by')->references('id')->on('users')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('contracts')) {
            Schema::create('contracts', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('booking_id');
                $table->integer('version')->default(1);
                $table->string('storage_path');
                $table->unsignedBigInteger('generated_by');
                $table->timestamp('signed_at')->nullable();
                $table->json('signature_meta')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('booking_id')->references('id')->on('bookings')->cascadeOnDelete();
                $table->foreign('generated_by')->references('id')->on('users')->nullOnDelete();
            });
        }

        if (! Schema::hasTable('vehicles')) {
            Schema::create('vehicles', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('plate_no')->unique();
                $table->string('model');
                $table->integer('capacity')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('drivers')) {
            Schema::create('drivers', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('full_name');
                $table->string('phone');
                $table->string('license_no')->unique();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('site_visits')) {
            Schema::create('site_visits', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('project_id');
                $table->date('visit_date');
                $table->string('time_slot');
                $table->string('meeting_point');
                $table->boolean('transport_included')->default(false);
                $table->bigInteger('transport_fee_tzs')->default(0);
                $table->uuid('vehicle_id')->nullable();
                $table->uuid('driver_id')->nullable();
                $table->enum('status', ['scheduled', 'completed', 'cancelled'])->default('scheduled');
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('project_id')->references('id')->on('projects')->cascadeOnDelete();
                $table->foreign('vehicle_id')->references('id')->on('vehicles')->nullOnDelete();
                $table->foreign('driver_id')->references('id')->on('drivers')->nullOnDelete();
            });
        }

        if (! Schema::hasTable('visit_passengers')) {
            Schema::create('visit_passengers', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('visit_id');
                $table->string('full_name');
                $table->string('phone');
                $table->integer('seats')->default(1);
                $table->json('qr_payload')->nullable();
                $table->string('pass_path')->nullable();
                $table->timestamp('checked_in_at')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('visit_id')->references('id')->on('site_visits')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('employees')) {
            Schema::create('employees', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->unsignedBigInteger('user_id');
                $table->string('employee_no')->unique();
                $table->string('position');
                $table->string('department');
                $table->date('hire_date');
                $table->bigInteger('monthly_salary_tzs')->default(0);
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('teams')) {
            Schema::create('teams', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('name');
                $table->unsignedBigInteger('lead_user_id')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('lead_user_id')->references('id')->on('users')->nullOnDelete();
            });
        }

        if (! Schema::hasTable('team_members')) {
            Schema::create('team_members', function (Blueprint $table) {
                $table->uuid('team_id');
                $table->unsignedBigInteger('user_id');
                $table->timestamps();
                $table->primary(['team_id', 'user_id']);

                $table->foreign('team_id')->references('id')->on('teams')->cascadeOnDelete();
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('tasks')) {
            Schema::create('tasks', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('title');
                $table->text('description')->nullable();
                $table->unsignedBigInteger('assignee_id')->nullable();
                $table->uuid('team_id')->nullable();
                $table->enum('status', ['todo', 'doing', 'review', 'done'])->default('todo');
                $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
                $table->timestamp('due_at')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('assignee_id')->references('id')->on('users')->nullOnDelete();
                $table->foreign('team_id')->references('id')->on('teams')->nullOnDelete();
                $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            });
        }

        if (! Schema::hasTable('calendar_events')) {
            Schema::create('calendar_events', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('title');
                $table->timestamp('starts_at');
                $table->timestamp('ends_at')->nullable();
                $table->enum('type', ['visit', 'meeting', 'deadline', 'other'])->default('other');
                $table->string('ref_type')->nullable();
                $table->uuid('ref_id')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->unsignedBigInteger('user_id');
                $table->enum('channel', ['in_app', 'sms', 'email'])->default('in_app');
                $table->string('title');
                $table->text('body');
                $table->timestamp('read_at')->nullable();
                $table->json('meta')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->unsignedBigInteger('actor_user_id')->nullable();
                $table->string('action');
                $table->string('entity_type');
                $table->uuid('entity_id')->nullable();
                $table->json('before')->nullable();
                $table->json('after')->nullable();
                $table->string('ip')->nullable();
                $table->string('ua')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('actor_user_id')->references('id')->on('users')->nullOnDelete();
            });
        }

        if (! Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('key')->unique();
                $table->json('value')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO authenticated');
            DB::statement('GRANT ALL ON ALL TABLES IN SCHEMA public TO service_role');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('calendar_events');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('team_members');
        Schema::dropIfExists('teams');
        Schema::dropIfExists('employees');
        Schema::dropIfExists('drivers');
        Schema::dropIfExists('vehicles');
        Schema::dropIfExists('visit_passengers');
        Schema::dropIfExists('site_visits');
        Schema::dropIfExists('contracts');
        Schema::dropIfExists('receipts');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('plots');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('users');
    }
};

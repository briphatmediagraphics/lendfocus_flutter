<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->text('description')->nullable();
            $table->bigInteger('price')->default(0);
            $table->string('currency', 10)->default('TZS');
            $table->string('location');
            $table->enum('property_type', ['land', 'house', 'farm', 'office'])->default('land');
            $table->enum('status', ['available', 'reserved', 'sold', 'rented', 'pending'])->default('available');
            $table->integer('area_sqm')->nullable();
            $table->integer('bedrooms')->nullable();
            $table->integer('bathrooms')->nullable();
            $table->json('images')->nullable();
            $table->boolean('featured')->default(false);
            $table->timestamps();
        });

        Schema::create('inquiries', function (Blueprint $table) {
            $table->id();
            $table->uuid('property_id')->nullable();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone');
            $table->text('message');
            $table->enum('payment_option', ['full', 'installment'])->nullable();
            $table->boolean('callback_requested')->default(false);
            $table->enum('status', ['new', 'contacted', 'closed'])->default('new');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('property_id')->references('id')->on('properties')->nullOnDelete();
        });

        Schema::create('calendar_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('event_type')->default('announcement');
            $table->timestamp('start_at');
            $table->timestamp('end_at')->nullable();
            $table->string('location')->nullable();
            $table->boolean('is_official')->default(true);
            $table->timestamps();
        });

        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->string('client_name');
            $table->string('client_email')->nullable();
            $table->string('client_phone');
            $table->enum('appointment_type', ['site_visit', 'consultation'])->default('site_visit');
            $table->timestamp('scheduled_at');
            $table->uuid('property_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('property_id')->references('id')->on('properties')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
        Schema::dropIfExists('calendar_events');
        Schema::dropIfExists('inquiries');
        Schema::dropIfExists('properties');
    }
};

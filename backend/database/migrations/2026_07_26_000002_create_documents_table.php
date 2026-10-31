<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->uuid('property_id')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_path');
            $table->string('file_type'); // 'pdf', 'doc', 'image', etc.
            $table->string('mime_type');
            $table->bigInteger('file_size')->default(0);
            $table->string('uploaded_by')->default('admin');
            $table->timestamps();

            $table->foreign('property_id')->references('id')->on('properties')->nullOnDelete();
        });

        Schema::table('properties', function (Blueprint $table) {
            $table->text('features')->nullable()->after('bathrooms');
            $table->json('documents')->nullable()->after('images');
            $table->text('document_notes')->nullable()->after('documents');
        });

        Schema::table('calendar_events', function (Blueprint $table) {
            $table->json('attachments')->nullable()->after('description');
            $table->string('event_color', 7)->default('#3b82f6')->after('is_official');
        });
    }

    public function down(): void
    {
        Schema::table('calendar_events', function (Blueprint $table) {
            $table->dropColumn('attachments');
            $table->dropColumn('event_color');
        });

        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn('features');
            $table->dropColumn('documents');
            $table->dropColumn('document_notes');
        });

        Schema::dropIfExists('documents');
    }
};

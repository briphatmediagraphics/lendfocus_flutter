<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->enum('payment_method', ['mpesa', 'airtelmoney', 'tigopesa', 'halopesa', 'crdb', 'nmb', 'cash'])->nullable()->after('notes');
            $table->string('payment_reference')->nullable()->after('payment_method');
            $table->bigInteger('payment_amount_tzs')->nullable()->after('payment_reference');
            $table->timestamp('payment_submitted_at')->nullable()->after('payment_amount_tzs');
            $table->enum('payment_status', ['pending', 'approved', 'rejected'])->default('pending')->after('payment_submitted_at');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn([
                'payment_method',
                'payment_reference',
                'payment_amount_tzs',
                'payment_submitted_at',
                'payment_status',
            ]);
        });
    }
};

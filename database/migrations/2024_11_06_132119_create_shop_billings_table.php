<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('shop_billings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_id')->nullable(); // Shop ID as foreign key
            $table->string('company_name')->nullable(); // Company name
            $table->text('address')->nullable(); // Billing address
            $table->string('pincode', 100)->nullable(); // Pincode
            $table->unsignedBigInteger('gst_percentage')->nullable(); // GST Percentage ID (foreign key)
            $table->string('hsn_code')->nullable(); // HSN code
            $table->unsignedBigInteger('country_id')->nullable(); // Country ID
            $table->unsignedBigInteger('state_id')->nullable(); // State ID
            $table->unsignedBigInteger('district_id')->nullable(); // District ID
            $table->string('pin', 200)->nullable(); // Pin for location
            $table->string('gst')->nullable(); // GST number
            $table->string('currency')->nullable(); // Currency
            $table->timestamps(); // created_at and updated_at

            // Foreign key constraints (if needed)
            $table->foreign('shop_id')->references('id')->on('shops')->onDelete('cascade');
            $table->foreign('country_id')->references('id')->on('shop_countries')->onDelete('cascade');
            $table->foreign('state_id')->references('id')->on('shop_states')->onDelete('cascade');
            $table->foreign('district_id')->references('id')->on('shop_districts')->onDelete('cascade');
            $table->foreign('gst_percentage')->references('id')->on('gst_tax_percentages')->onDelete('set null'); // Foreign key for gst_percentage
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_billings');
    }
};

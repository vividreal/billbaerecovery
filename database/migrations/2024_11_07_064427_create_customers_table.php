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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_id');
            $table->string('customer_code', 100)->nullable();
            $table->string('name');
            $table->tinyInteger('gender')->nullable();
            $table->date('dob')->nullable();
            $table->string('billing_name')->nullable();
            $table->unsignedBigInteger('country_id')->nullable();
            $table->unsignedBigInteger('state_id')->nullable();
            $table->unsignedBigInteger('district_id')->nullable();
            $table->string('pincode')->nullable();
            $table->string('gst')->nullable();
            $table->string('mobile')->nullable();
            $table->unsignedInteger('phone_code')->nullable();
            $table->string('email')->nullable();
            $table->text('image')->nullable();
            $table->text('address')->nullable();
            $table->tinyInteger('status')->default(1); // 1 for active, 0 for inactive
            $table->enum('visiting_status', ['0', '1', '2', '3', '4'])->nullable();
            $table->enum('behavioral_status', ['0', '1', '2', '3', '4'])->nullable();
            $table->tinyInteger('is_membership_holder')->default(0);
            $table->timestamps();
            $table->softDeletes(); // Adds 'deleted_at' column for soft deletes
            $table->tinyInteger('is_instore_credit')->default(0); // 1 for credit, 0 for no credit

            // Add Foreign Key Constraints
            $table->foreign('shop_id')->references('id')->on('shops')->onDelete('cascade');
            $table->foreign('country_id')->references('id')->on('shop_countries')->onDelete('set null');
            $table->foreign('state_id')->references('id')->on('shop_states')->onDelete('set null');
            $table->foreign('district_id')->references('id')->on('shop_districts')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};

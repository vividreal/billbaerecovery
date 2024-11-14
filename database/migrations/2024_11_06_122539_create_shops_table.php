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
        Schema::create('shops', function (Blueprint $table) {
            $table->id(); // This creates an auto-incrementing bigint unsigned 'id' field by default
           // $table->integer('user_id'); // User associated with the shop
            $table->string('name'); // Shop name
            $table->string('email')->nullable(); // Shop email
            $table->string('contact', 100)->nullable(); // Shop contact number
            $table->text('address')->nullable(); // Shop address
            $table->string('pincode', 100)->nullable(); // Shop pincode
            $table->unsignedBigInteger('country_id')->nullable(); // Country ID (unsignedBigInteger)
            $table->unsignedBigInteger('state_id')->nullable(); // State ID (unsignedBigInteger)
            $table->unsignedBigInteger('district_id')->nullable(); // District ID (unsignedBigInteger)
            $table->string('pin', 200)->nullable(); // Pin for location
            $table->text('image')->nullable(); // Shop image
            $table->text('location')->nullable(); // Location information
            $table->longText('map_location')->nullable(); // Map location
            $table->string('timezone')->nullable(); // Timezone of the shop
            $table->string('time_format', 10)->default('1'); // Time format
            $table->unsignedBigInteger('business_type_id')->nullable(); // Business type (unsignedBigInteger)
            $table->tinyInteger('store_type')->nullable()->comment('1 = Single, 2 = With branches'); // Store type
            $table->text('about')->nullable(); // About the shop
            $table->tinyInteger('active')->default(0)->comment('0 = Inactive, 1 = Active'); // Shop active status
            $table->tinyInteger('status')->default(1)->comment('1 = Active'); // Shop status
            $table->timestamps(); // created_at, updated_at
            $table->timestamp('deleted_at')->nullable(); // Soft delete

            // Foreign keys
            $table->foreign('country_id')->references('id')->on('shop_countries')->onDelete('cascade');
            $table->foreign('state_id')->references('id')->on('shop_states')->onDelete('cascade');
            $table->foreign('district_id')->references('id')->on('shop_districts')->onDelete('cascade');
            $table->foreign('business_type_id')->references('id')->on('business_types')->onDelete('cascade'); // Added the foreign key for business_type_id
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shops');
    }
};

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
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products'); // Foreign key to products table
            $table->foreignId('shop_id')->constrained('shops'); // Foreign key to shops table
            $table->foreignId('staffprofile_id')->constrained('staff_profiles'); // Foreign key to staffprofiles table
            $table->foreignId('service_id')->nullable()->constrained('services'); // Foreign key to services table (nullable)
            $table->foreignId('package_id')->nullable()->constrained('packages'); // Foreign key to packages table (nullable)
            $table->integer('quantity'); // Quantity (int)
            $table->integer('taking_quantity')->default(0); // Taking quantity (int, default 0)
            $table->timestamps(); // created_at and updated_at columns
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};

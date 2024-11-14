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
        Schema::create('additionaltaxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops'); // Foreign key to 'shops' table
            $table->string('name'); // Name of the tax
            $table->integer('percentage'); // Tax percentage
            $table->text('information')->nullable(); // Additional information (nullable)
            $table->timestamps(0); // created_at and updated_at columns
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('additionaltaxes');
    }
};

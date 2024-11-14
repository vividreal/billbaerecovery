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
        Schema::create('products', function (Blueprint $table) {
            $table->id(); 
            $table->unsignedBigInteger('shop_id');
            $table->unsignedBigInteger('category_id');
            $table->string('name', 255);
            $table->string('product_code', 255);
            $table->string('image', 255)->nullable();
            $table->string('bill_image', 255)->nullable();
            $table->string('product_type', 255)->default('reuse');
            $table->decimal('price', 8, 2);
            $table->string('description', 255)->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('shop_id')->references('id')->on('shops')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

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
        Schema::create('packages', function (Blueprint $table) {
            $table->id(); 
            $table->unsignedBigInteger('shop_id');
            $table->string('name', 255);
            $table->string('slug', 255);
            $table->double('price', 8, 2);
            $table->double('service_price')->nullable();
            $table->double('discount')->nullable();
            $table->string('instore_credit_amount')->nullable();
            $table->integer('validity_mode')->nullable();
            $table->integer('validity')->nullable();
            $table->string('validity_from')->nullable();
            $table->string('validity_to')->nullable();
            $table->tinyInteger('tax_included')->nullable();
            $table->unsignedBigInteger('gst_tax')->nullable();  // Ensure unsignedBigInteger
            $table->string('hsn_code', 100)->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamp('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
    
            // Foreign key constraints
            $table->foreign('shop_id')->references('id')->on('shops')->onDelete('cascade');
            $table->foreign('gst_tax')->references('id')->on('gst_tax_percentages')->onDelete('set null');  // Fix here
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};

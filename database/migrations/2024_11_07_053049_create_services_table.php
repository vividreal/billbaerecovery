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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_id');
            $table->unsignedBigInteger('service_category_id')->nullable();
            $table->string('name', 255);
            $table->string('slug', 255);
            $table->unsignedBigInteger('hours_id');  // Ensure this matches `hours.id` data type
            $table->double('price', 8, 2);
            $table->unsignedBigInteger('lead_before')->nullable();
            $table->unsignedBigInteger('lead_after')->nullable();
            $table->tinyInteger('tax_included')->nullable()->comment('1 - Included, 0 - Excluded');
            $table->unsignedBigInteger('gst_tax')->nullable();
            $table->string('hsn_code', 100)->nullable();
            $table->tinyInteger('status')->default(1);
            $table->unsignedInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign key constraints
            $table->foreign('shop_id')->references('id')->on('shops')->onDelete('cascade');
            $table->foreign('service_category_id')->references('id')->on('service_categories')->onDelete('set null');
            $table->foreign('hours_id')->references('id')->on('hours')->onDelete('cascade');
            $table->foreign('lead_before')->references('id')->on('hours')->onDelete('set null');
            $table->foreign('lead_after')->references('id')->on('hours')->onDelete('set null');
            $table->foreign('gst_tax')->references('id')->on('gst_tax_percentages')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};

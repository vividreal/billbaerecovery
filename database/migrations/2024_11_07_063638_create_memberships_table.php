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
        Schema::create('memberships', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 8, 2);
            $table->decimal('membership_price', 8, 2);
            $table->text('duration_type');
            $table->integer('duration_in_days');
            $table->unsignedBigInteger('gst_id')->nullable(); // Make sure nullable
            $table->tinyInteger('expiry_status');
            $table->string('is_tax_included')->nullable();
            $table->timestamps();
    
            // Foreign key for gst_id
            $table->foreign('gst_id')
                  ->references('id')
                  ->on('gst_tax_percentages') // Ensure the table exists and is referenced correctly
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('memberships');
    }
};

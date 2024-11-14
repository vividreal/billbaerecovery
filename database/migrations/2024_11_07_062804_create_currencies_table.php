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
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('country_id')->nullable(); // Correct data type for foreign key
            $table->string('code')->nullable();
            $table->text('symbol')->nullable();
            $table->timestamps();

            // Define the foreign key relationship to the countries table
            $table->foreign('country_id')
                  ->references('id')
                  ->on('shop_countries')
                  ->onDelete('set null'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};

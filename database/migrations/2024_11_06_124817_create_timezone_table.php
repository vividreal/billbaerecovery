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
        Schema::create('timezone', function (Blueprint $table) {
            $table->id();
            $table->integer('zone_id'); // Zone ID as integer
            $table->char('country_code', 2); // Country code as a 2-character string
            $table->string('zone_name', 35); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timezone');
    }
};

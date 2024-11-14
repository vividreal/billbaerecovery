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
        Schema::create('shop_states', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('country_id')->default(1);
            $table->string('name', 30);            
            $table->timestamps();
            $table->foreign('country_id')->references('id')->on('shop_countries')
            ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shop_states', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
        });
        Schema::dropIfExists('shop_states');
    }
};

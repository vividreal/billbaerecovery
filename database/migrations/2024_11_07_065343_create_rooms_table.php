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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Room name (VARCHAR(255))
            $table->foreignId('shop_id')->constrained('shops')->onDelete('cascade'); // Foreign key to shops table
            $table->tinyInteger('status')->default(1); // Room status (tinyint, default 1)
            $table->longText('description')->nullable(); // Room description (LONGTEXT, nullable)
            $table->timestamps(); // created_at and updated_at columns

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};

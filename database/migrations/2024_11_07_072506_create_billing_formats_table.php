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
        Schema::create('billing_formats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shops'); // Foreign key to 'shops' table
            $table->tinyInteger('applied_to_all')->default(1); // Default value of 1
            $table->string('prefix', 10)->nullable(); // Nullable prefix
            $table->integer('suffix')->nullable(); // Nullable suffix
            $table->foreignId('payment_type')->nullable()->constrained('payment_types', 'id'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billing_formats');
    }
};

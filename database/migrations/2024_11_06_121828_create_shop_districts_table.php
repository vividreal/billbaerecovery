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
        Schema::create('shop_districts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('state_id'); 
            $table->string('name', 30);            
            $table->timestamps(); // This will automatically add `created_at` and `updated_at`

            // Foreign key constraint linking state_id to shop_states.id
            $table->foreign('state_id')->references('id')->on('shop_states')
                ->onDelete('cascade'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shop_districts', function (Blueprint $table) {
            $table->dropForeign(['state_id']); // Drop the foreign key constraint
        });
        Schema::dropIfExists('shop_districts');
    }
};

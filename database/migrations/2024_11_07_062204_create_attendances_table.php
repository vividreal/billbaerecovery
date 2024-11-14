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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('staff_id')->unsigned()->nullable();
            $table->bigInteger('user_id')->unsigned();
            $table->bigInteger('marked_by')->unsigned()->nullable();
            $table->timestamp('in_time')->nullable();
            $table->timestamp('out_time')->nullable();
            $table->timestamp('total_time')->nullable();
            $table->timestamps();
    
            // Foreign Key Constraints
            $table->foreign('staff_id')->references('id')->on('staff_profiles')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('marked_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Drop foreign key constraints
            $table->dropForeign(['staff_id']);
            $table->dropForeign(['user_id']);
            $table->dropForeign(['marked_by']);
        });
        Schema::dropIfExists('attendances');
    }
};

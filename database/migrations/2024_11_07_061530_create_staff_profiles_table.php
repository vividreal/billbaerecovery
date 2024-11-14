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
        Schema::create('staff_profiles', function (Blueprint $table) {
            $table->id();  
            $table->unsignedBigInteger('user_id'); // Ensure user_id is unsignedBigInteger
            $table->unsignedBigInteger('designation')->nullable(); // Ensure designation is unsignedBigInteger
            $table->tinyInteger('is_staff')->nullable();
            $table->date('dob')->nullable();
            $table->date('joining_date')->nullable();
            $table->date('contract_end_date')->nullable();
            $table->unsignedInteger('schedule_color')->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('designation')->references('id')->on('designations')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_profiles');
    }
};

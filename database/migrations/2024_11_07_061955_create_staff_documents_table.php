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
        Schema::create('staff_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // Ensure user_id is unsignedBigInteger
            $table->text('name')->nullable();
            $table->text('details')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->unsignedBigInteger('uploaded_by')->nullable(); // Ensure uploaded_by is unsignedBigInteger
            $table->timestamps();

            // Foreign key constraint if needed (assumes users table exists)
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('uploaded_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_documents');
    }
};

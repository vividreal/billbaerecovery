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
        Schema::create('cashbooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->nullable()->constrained('shops'); // Foreign key to the 'shops' table (nullable)
            $table->tinyInteger('cash_book')->nullable()->comment('1 - Business Cash, 2 Petty Cash'); // Cash book type
            $table->decimal('transaction_amount', 10, 0)->nullable(); // Transaction amount
            $table->decimal('balance_amount', 10, 0)->nullable(); // Balance amount
            $table->tinyInteger('transaction')->nullable()->comment('1 - Credit, 2- Debit'); // Transaction type
            $table->tinyInteger('cash_from')->default(0)->comment('0 - Cash deposit, 1 - From Sales'); // Cash source
            $table->text('message')->nullable(); // Additional message
            $table->integer('system_message')->nullable(); // System message ID
            $table->integer('done_by')->nullable(); // User who performed the transaction
            $table->timestamps(0); // created_at and updated_at columns
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cashbooks');
    }
};

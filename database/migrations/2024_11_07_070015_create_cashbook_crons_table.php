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
        Schema::create('cashbook_crons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cashbook_id')->nullable()->constrained('cashbooks'); // Foreign key to the 'cashbooks' table (nullable)
            $table->double('opening_business_cash_balance')->nullable(); // Opening business cash balance
            $table->double('closing_business_cash_balance')->nullable(); // Closing business cash balance
            $table->double('opening_petty_cash_balance')->nullable(); // Opening petty cash balance
            $table->double('closing_petty_cash_balance')->nullable(); // Closing petty cash balance
            $table->timestamp('cashbook_date')->nullable(); // Cashbook date
            $table->tinyInteger('status')->nullable(); // Status
            $table->timestamps(0); // created_at and updated_at columns
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cashbook_crons');
    }
};

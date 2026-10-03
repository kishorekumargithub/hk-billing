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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_id')->constrained();
            $table->foreignId('customer_id')->constrained();
            $table->date('period_start');
            $table->date('period_end');
            $table->unsignedBigInteger('total');                      // paise
            $table->string('status')->default('draft');
            $table->timestamps();
            $table->unique(['customer_id', 'period_start']);          // prevents duplicate invoices
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};

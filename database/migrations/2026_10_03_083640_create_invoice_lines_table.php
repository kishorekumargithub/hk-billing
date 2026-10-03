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
        Schema::create('invoice_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_id')->constrained();
            $table->json('plan_snapshot');                            // price at billing time
            $table->date('segment_start');
            $table->date('segment_end');
            $table->unsignedSmallInteger('days_active');
            $table->unsignedSmallInteger('days_in_cycle');
            $table->unsignedBigInteger('base_fee');
            $table->unsignedBigInteger('usage_units');
            $table->unsignedBigInteger('allowance');
            $table->unsignedBigInteger('overage_units');
            $table->unsignedBigInteger('overage_charge');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_lines');
    }
};

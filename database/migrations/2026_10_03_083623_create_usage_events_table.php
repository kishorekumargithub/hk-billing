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
        Schema::create('usage_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('merchant_id');
            $table->unsignedBigInteger('customer_id');
            $table->unsignedInteger('units');
            $table->date('usage_date');
            $table->string('idempotency_key', 64);
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['merchant_id', 'idempotency_key'], 'uniq_idem');    // idempotency guarantee
            $table->index(['customer_id', 'usage_date'], 'idx_cust_date');       // aggregation + range scans
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usage_events');
    }
};

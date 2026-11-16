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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('receipt_no')->nullable()->unique()->after('invoice_date');
            $table->date('receipt_date')->nullable()->after('receipt_no');
            $table->string('receipt_payment_for')->nullable()->after('receipt_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['receipt_no', 'receipt_date', 'receipt_payment_for']);
        });
    }
};

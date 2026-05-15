<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_status')->default('pending')->after('status');
            $table->string('payment_method')->nullable()->after('payment_status');
            $table->string('payment_provider')->nullable()->after('payment_method');
            $table->string('payment_reference')->nullable()->after('payment_provider');
            $table->timestamp('paid_at')->nullable()->after('payment_reference');
            $table->string('shipping_courier')->nullable()->after('shipping_snapshot');
            $table->string('shipping_receipt')->nullable()->after('shipping_courier');
            $table->timestamp('shipped_at')->nullable()->after('shipping_receipt');
            $table->timestamp('completed_at')->nullable()->after('shipped_at');
            $table->timestamp('cancelled_at')->nullable()->after('completed_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'payment_status',
                'payment_method',
                'payment_provider',
                'payment_reference',
                'paid_at',
                'shipping_courier',
                'shipping_receipt',
                'shipped_at',
                'completed_at',
                'cancelled_at',
            ]);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add voucher columns to orders table
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'voucher_id')) {
                $table->unsignedBigInteger('voucher_id')->nullable()->after('indent_status');
            }
            if (!Schema::hasColumn('orders', 'voucher_code')) {
                $table->string('voucher_code')->nullable()->after('voucher_id');
            }
            if (!Schema::hasColumn('orders', 'discount_amount')) {
                $table->decimal('discount_amount', 12, 2)->unsigned()->default(0)->after('voucher_code');
            }
        });

        // Add voucher columns to carts table (as session/column data is stored JSON, we'll add to cart_items)
        // Actually, voucher info is applied at checkout, so we add to order items and cart items
        // For now, we'll track voucher at the order level. Cart voucher will be tracked when checkout starts.

        // Add voucher columns to cart_items table
        Schema::table('cart_items', function (Blueprint $table) {
            if (!Schema::hasColumn('cart_items', 'voucher_id')) {
                $table->unsignedBigInteger('voucher_id')->nullable()->after('indent_quantity');
            }
            if (!Schema::hasColumn('cart_items', 'voucher_code')) {
                $table->string('voucher_code')->nullable()->after('voucher_id');
            }
            if (!Schema::hasColumn('cart_items', 'discount_amount')) {
                $table->decimal('discount_amount', 12, 2)->unsigned()->default(0)->after('voucher_code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            if (Schema::hasColumn('cart_items', 'voucher_id')) {
                $table->dropColumn('voucher_id');
            }
            if (Schema::hasColumn('cart_items', 'voucher_code')) {
                $table->dropColumn('voucher_code');
            }
            if (Schema::hasColumn('cart_items', 'discount_amount')) {
                $table->dropColumn('discount_amount');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'voucher_id')) {
                $table->dropColumn('voucher_id');
            }
            if (Schema::hasColumn('orders', 'voucher_code')) {
                $table->dropColumn('voucher_code');
            }
            if (Schema::hasColumn('orders', 'discount_amount')) {
                $table->dropColumn('discount_amount');
            }
        });
    }
};
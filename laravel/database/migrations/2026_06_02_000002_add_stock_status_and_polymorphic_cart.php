<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add stock_status to motors
        Schema::table('motors', function (Blueprint $table) {
            if (!Schema::hasColumn('motors', 'stock_status')) {
                $table->string('stock_status')->default('ready')->after('status');
            }
        });

        // 2. Add stock_status to parts
        Schema::table('parts', function (Blueprint $table) {
            if (!Schema::hasColumn('parts', 'stock_status')) {
                $table->string('stock_status')->default('ready')->after('status');
            }
        });

        // 3. Add polymorphic fields to cart_items
        Schema::table('cart_items', function (Blueprint $table) {
            if (!Schema::hasColumn('cart_items', 'itemable_type')) {
                $table->string('itemable_type')->nullable()->after('part_variant_id');
            }
            if (!Schema::hasColumn('cart_items', 'itemable_id')) {
                $table->unsignedBigInteger('itemable_id')->nullable()->after('itemable_type');
            }
            if (!Schema::hasColumn('cart_items', 'product_name')) {
                $table->string('product_name')->nullable()->after('quantity');
            }
            if (!Schema::hasColumn('cart_items', 'variant_name')) {
                $table->string('variant_name')->nullable()->after('product_name');
            }
            if (!Schema::hasColumn('cart_items', 'image_path')) {
                $table->string('image_path')->nullable()->after('variant_name');
            }
            $table->index(['itemable_type', 'itemable_id']);
        });

        // 4. Add polymorphic fields to order_items
        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'itemable_type')) {
                $table->string('itemable_type')->nullable()->after('part_variant_id');
            }
            if (!Schema::hasColumn('order_items', 'itemable_id')) {
                $table->unsignedBigInteger('itemable_id')->nullable()->after('itemable_type');
            }
            $table->index(['itemable_type', 'itemable_id']);
        });

        // 5. Add indent/DP fields to orders
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'dp_amount')) {
                $table->decimal('dp_amount', 12, 2)->default(0)->after('total');
            }
            if (!Schema::hasColumn('orders', 'remaining_amount')) {
                $table->decimal('remaining_amount', 12, 2)->default(0)->after('dp_amount');
            }
            if (!Schema::hasColumn('orders', 'is_indent')) {
                $table->boolean('is_indent')->default(false)->after('remaining_amount');
            }
            if (!Schema::hasColumn('orders', 'indent_status')) {
                $table->string('indent_status')->nullable()->after('is_indent');
            }
        });

        // 6. Add sort_order to motor_360_images (already has it from previous migration)
    }

    public function down(): void
    {
        Schema::table('motors', function (Blueprint $table) {
            $table->dropColumn('stock_status');
        });
        Schema::table('parts', function (Blueprint $table) {
            $table->dropColumn('stock_status');
        });
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropIndex(['itemable_type', 'itemable_id']);
            $table->dropColumn(['itemable_type', 'itemable_id', 'product_name', 'variant_name', 'image_path']);
        });
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex(['itemable_type', 'itemable_id']);
            $table->dropColumn(['itemable_type', 'itemable_id']);
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['dp_amount', 'remaining_amount', 'is_indent', 'indent_status']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('cart_items', 'indent_quantity')) {
            Schema::table('cart_items', function (Blueprint $table) {
                $table->unsignedInteger('indent_quantity')->nullable()->default(0)->after('quantity');
            });
        }

        if (!Schema::hasColumn('order_items', 'indent_quantity')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->unsignedInteger('indent_quantity')->nullable()->default(0)->after('quantity');
            });
        }
    }

    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            if (Schema::hasColumn('cart_items', 'indent_quantity')) {
                $table->dropColumn('indent_quantity');
            }
        });

        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'indent_quantity')) {
                $table->dropColumn('indent_quantity');
            }
        });
    }
};

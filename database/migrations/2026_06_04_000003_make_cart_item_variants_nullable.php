<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            if (Schema::hasColumn('cart_items', 'motor_color_id')) {
                $table->unsignedBigInteger('motor_color_id')->nullable()->change();
            }
            if (Schema::hasColumn('cart_items', 'part_variant_id')) {
                $table->unsignedBigInteger('part_variant_id')->nullable()->change();
            }
        });

        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'motor_color_id')) {
                $table->unsignedBigInteger('motor_color_id')->nullable()->change();
            }
            if (Schema::hasColumn('order_items', 'part_variant_id')) {
                $table->unsignedBigInteger('part_variant_id')->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            if (Schema::hasColumn('cart_items', 'motor_color_id')) {
                $table->unsignedBigInteger('motor_color_id')->nullable(false)->change();
            }
            if (Schema::hasColumn('cart_items', 'part_variant_id')) {
                $table->unsignedBigInteger('part_variant_id')->nullable(false)->change();
            }
        });

        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'motor_color_id')) {
                $table->unsignedBigInteger('motor_color_id')->nullable(false)->change();
            }
            if (Schema::hasColumn('order_items', 'part_variant_id')) {
                $table->unsignedBigInteger('part_variant_id')->nullable(false)->change();
            }
        });
    }
};

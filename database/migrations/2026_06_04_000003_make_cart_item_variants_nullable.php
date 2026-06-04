<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->unsignedBigInteger('motor_color_id')->nullable()->change();
            $table->unsignedBigInteger('part_variant_id')->nullable()->change();
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedBigInteger('motor_color_id')->nullable()->change();
            $table->unsignedBigInteger('part_variant_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->unsignedBigInteger('motor_color_id')->nullable(false)->change();
            $table->unsignedBigInteger('part_variant_id')->nullable(false)->change();
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedBigInteger('motor_color_id')->nullable(false)->change();
            $table->unsignedBigInteger('part_variant_id')->nullable(false)->change();
        });
    }
};

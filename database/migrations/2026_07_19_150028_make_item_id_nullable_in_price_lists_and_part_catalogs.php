<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('item_price_lists', function (Blueprint $table) {
            $table->dropForeign(['item_id']);
        });

        Schema::table('item_price_lists', function (Blueprint $table) {
            $table->foreignId('item_id')->nullable()->change();
        });

        Schema::table('item_part_catalogs', function (Blueprint $table) {
            $table->dropForeign(['item_id']);
        });

        Schema::table('item_part_catalogs', function (Blueprint $table) {
            $table->foreignId('item_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('item_part_catalogs', function (Blueprint $table) {
            $table->foreignId('item_id')->nullable(false)->change();
        });

        Schema::table('item_part_catalogs', function (Blueprint $table) {
            $table->foreign('item_id')->references('id')->on('items')->cascadeOnDelete();
        });

        Schema::table('item_price_lists', function (Blueprint $table) {
            $table->foreignId('item_id')->nullable(false)->change();
        });

        Schema::table('item_price_lists', function (Blueprint $table) {
            $table->foreign('item_id')->references('id')->on('items')->cascadeOnDelete();
        });
    }
};

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
        if (Schema::hasTable('item_price_lists') && ! Schema::hasColumn('item_price_lists', 'image_path')) {
            Schema::table('item_price_lists', function (Blueprint $table) {
                $table->string('image_path')->nullable()->after('pdf_path');
            });
        }

        if (Schema::hasTable('item_part_catalogs') && ! Schema::hasColumn('item_part_catalogs', 'image_path')) {
            Schema::table('item_part_catalogs', function (Blueprint $table) {
                $table->string('image_path')->nullable()->after('pdf_path');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('item_price_lists') && Schema::hasColumn('item_price_lists', 'image_path')) {
            Schema::table('item_price_lists', function (Blueprint $table) {
                $table->dropColumn('image_path');
            });
        }

        if (Schema::hasTable('item_part_catalogs') && Schema::hasColumn('item_part_catalogs', 'image_path')) {
            Schema::table('item_part_catalogs', function (Blueprint $table) {
                $table->dropColumn('image_path');
            });
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('item_part_catalogs', function (Blueprint $table) {
            if (! Schema::hasColumn('item_part_catalogs', 'brand_id')) {
                $table->foreignId('brand_id')
                    ->nullable()
                    ->after('item_id')
                    ->constrained('brands')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('item_part_catalogs', 'slug')) {
                $table->string('slug')->nullable()->after('name')->index();
            }
        });

        if (! Schema::hasTable('item_part_catalog_images')) {
            Schema::create('item_part_catalog_images', function (Blueprint $table) {
                $table->id();
                $table->foreignId('item_part_catalog_id')
                    ->constrained('item_part_catalogs')
                    ->cascadeOnDelete();
                $table->string('path');
                $table->unsignedInteger('sort_order')->default(0)->index();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('item_part_catalog_images');

        Schema::table('item_part_catalogs', function (Blueprint $table) {
            if (Schema::hasColumn('item_part_catalogs', 'slug')) {
                $table->dropColumn('slug');
            }
            if (Schema::hasColumn('item_part_catalogs', 'brand_id')) {
                $table->dropConstrainedForeignId('brand_id');
            }
        });
    }
};

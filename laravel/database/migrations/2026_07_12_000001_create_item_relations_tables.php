<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Item Images (replaces motor_images)
        Schema::create('item_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->string('path');
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });

        // Item Colors (replaces motor_colors)
        Schema::create('item_colors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->string('name');
            $table->string('color_code', 7)->nullable();
            $table->string('image_path')->nullable();
            $table->unsignedInteger('weight')->nullable();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });

        // Item Specifications (replaces motor_specifications)
        Schema::create('item_specifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->string('group')->index();
            $table->string('key');
            $table->text('value');
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });

        // Item 360 Images (replaces motor_360_images)
        Schema::create('item_360_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->string('path');
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });

        // Item Price Lists (replaces price_lists for motors)
        Schema::create('item_price_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->string('name');
            $table->string('pdf_path');
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });

        // Item Part Catalogs (replaces part_catalogs for motors)
        Schema::create('item_part_catalogs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->string('name');
            $table->string('pdf_path');
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_part_catalogs');
        Schema::dropIfExists('item_price_lists');
        Schema::dropIfExists('item_360_images');
        Schema::dropIfExists('item_specifications');
        Schema::dropIfExists('item_colors');
        Schema::dropIfExists('item_images');
    }
};

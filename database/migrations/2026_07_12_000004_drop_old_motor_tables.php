<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop tables that reference motors first (respect FK order)
        Schema::dropIfExists('product_highlights');
        Schema::dropIfExists('motor_360_images');
        Schema::dropIfExists('motor_specifications');
        Schema::dropIfExists('motor_colors');
        Schema::dropIfExists('motor_images');
        Schema::dropIfExists('motor_part');
        Schema::dropIfExists('price_lists');
        Schema::dropIfExists('part_catalogs');

        // Drop the main motor tables
        Schema::dropIfExists('motors');
        Schema::dropIfExists('motor_categories');
    }

    public function down(): void
    {
        // Note: This is a destructive migration. The down() method is provided
        // but restoring dropped tables with data is not possible via migration.
        // This is intentional as part of the restruktur-kategori-dinamis feature.
    }
};

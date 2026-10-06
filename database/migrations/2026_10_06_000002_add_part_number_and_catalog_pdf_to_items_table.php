<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            if (! Schema::hasColumn('items', 'part_number')) {
                $table->string('part_number', 64)->nullable()->after('thumbnail_path')->index();
            }

            if (! Schema::hasColumn('items', 'catalog_pdf_path')) {
                $table->string('catalog_pdf_path')->nullable()->after('thumbnail_path');
            }
        });

        if (! Schema::hasTable('item_motor_compatibility')) {
            Schema::create('item_motor_compatibility', function (Blueprint $table) {
                $table->foreignId('item_id')->constrained('items')->cascadeOnDelete(); // sparepart item
                $table->foreignId('motor_id')->constrained('items')->cascadeOnDelete(); // motor item
                $table->timestamps();
                $table->primary(['item_id', 'motor_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('item_motor_compatibility');

        Schema::table('items', function (Blueprint $table) {
            if (Schema::hasColumn('items', 'part_number')) {
                $table->dropColumn('part_number');
            }

            if (Schema::hasColumn('items', 'catalog_pdf_path')) {
                $table->dropColumn('catalog_pdf_path');
            }
        });
    }
};

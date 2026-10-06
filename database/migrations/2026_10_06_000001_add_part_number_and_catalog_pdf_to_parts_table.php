<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parts', function (Blueprint $table) {
            if (! Schema::hasColumn('parts', 'part_number')) {
                $table->string('part_number', 64)->nullable()->after('sku')->index();
            }

            if (! Schema::hasColumn('parts', 'catalog_pdf_path')) {
                $table->string('catalog_pdf_path')->nullable()->after('thumbnail_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('parts', function (Blueprint $table) {
            if (Schema::hasColumn('parts', 'part_number')) {
                $table->dropColumn('part_number');
            }

            if (Schema::hasColumn('parts', 'catalog_pdf_path')) {
                $table->dropColumn('catalog_pdf_path');
            }
        });
    }
};

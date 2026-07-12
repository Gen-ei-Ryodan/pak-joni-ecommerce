<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parts', function (Blueprint $table) {
            if (!Schema::hasColumn('parts', 'category_type_id')) {
                $table->foreignId('category_type_id')->nullable()->after('id')
                    ->constrained('category_types')->nullOnDelete();
            }
        });

        Schema::table('part_categories', function (Blueprint $table) {
            if (!Schema::hasColumn('part_categories', 'category_type_id')) {
                $table->foreignId('category_type_id')->nullable()->after('id')
                    ->constrained('category_types')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('parts', function (Blueprint $table) {
            if (Schema::hasColumn('parts', 'category_type_id')) {
                $table->dropForeign(['category_type_id']);
                $table->dropColumn('category_type_id');
            }
        });

        Schema::table('part_categories', function (Blueprint $table) {
            if (Schema::hasColumn('part_categories', 'category_type_id')) {
                $table->dropForeign(['category_type_id']);
                $table->dropColumn('category_type_id');
            }
        });
    }
};

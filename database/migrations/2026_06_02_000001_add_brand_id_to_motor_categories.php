<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('motor_categories', function (Blueprint $table) {
            if (!Schema::hasColumn('motor_categories', 'brand_id')) {
                $table->foreignId('brand_id')->nullable()->after('id')->constrained('brands')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('motor_categories', function (Blueprint $table) {
            if (Schema::hasColumn('motor_categories', 'brand_id')) {
                $table->dropForeign(['brand_id']);
                $table->dropColumn('brand_id');
            }
        });
    }
};

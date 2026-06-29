<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('part_variants', function (Blueprint $table) {
            $table->unsignedInteger('weight')->default(0)->after('stock');
        });

        Schema::table('motor_colors', function (Blueprint $table) {
            $table->unsignedInteger('weight')->default(0)->after('image_path');
        });
    }

    public function down(): void
    {
        Schema::table('part_variants', function (Blueprint $table) {
            $table->dropColumn('weight');
        });

        Schema::table('motor_colors', function (Blueprint $table) {
            $table->dropColumn('weight');
        });
    }
};

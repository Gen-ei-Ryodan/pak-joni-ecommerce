<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->unsignedBigInteger('mforce_id')->nullable()->unique()->after('id');
            $table->index('mforce_id');
        });

        Schema::table('item_colors', function (Blueprint $table) {
            $table->unsignedBigInteger('mforce_id')->nullable()->after('id');
            $table->unique(['item_id', 'mforce_id'], 'uq_item_color_mforce');
        });

        Schema::table('item_images', function (Blueprint $table) {
            $table->unsignedBigInteger('mforce_id')->nullable()->after('id');
            $table->unique(['item_id', 'mforce_id'], 'uq_item_image_mforce');
        });

        Schema::table('item_specifications', function (Blueprint $table) {
            $table->unsignedBigInteger('mforce_id')->nullable()->after('id');
            $table->unique(['item_id', 'mforce_id'], 'uq_item_spec_mforce');
        });

        Schema::table('item_360_images', function (Blueprint $table) {
            $table->unsignedBigInteger('mforce_id')->nullable()->after('id');
            $table->unique(['item_id', 'mforce_id'], 'uq_item_360_mforce');
        });
    }

    public function down(): void
    {
        Schema::table('item_360_images', function (Blueprint $table) {
            $table->dropUnique('uq_item_360_mforce');
            $table->dropColumn('mforce_id');
        });

        Schema::table('item_specifications', function (Blueprint $table) {
            $table->dropUnique('uq_item_spec_mforce');
            $table->dropColumn('mforce_id');
        });

        Schema::table('item_images', function (Blueprint $table) {
            $table->dropUnique('uq_item_image_mforce');
            $table->dropColumn('mforce_id');
        });

        Schema::table('item_colors', function (Blueprint $table) {
            $table->dropUnique('uq_item_color_mforce');
            $table->dropColumn('mforce_id');
        });

        Schema::table('items', function (Blueprint $table) {
            $table->dropIndex(['mforce_id']);
            $table->dropUnique(['mforce_id']);
            $table->dropColumn('mforce_id');
        });
    }
};

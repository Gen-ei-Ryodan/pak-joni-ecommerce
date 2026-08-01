<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('item_colors', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('sort_order');
        });

        Schema::table('item_images', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('sort_order');
        });

        Schema::table('item_specifications', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('sort_order');
        });

        Schema::table('item_360_images', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('sort_order');
        });

        Schema::create('mforce_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->string('sync_type')->default('all'); // all | single_brand
            $table->string('brand_slug')->nullable();
            $table->string('trigger')->default('cli');   // cli | scheduler | admin
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->unsignedInteger('created')->default(0);
            $table->unsignedInteger('updated')->default(0);
            $table->unsignedInteger('skipped')->default(0);
            $table->unsignedInteger('archived')->default(0);
            $table->unsignedInteger('errors')->default(0);
            $table->text('error_details')->nullable();
            $table->string('status')->default('running'); // running | success | failed
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mforce_sync_logs');

        Schema::table('item_360_images', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
        Schema::table('item_specifications', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
        Schema::table('item_images', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
        Schema::table('item_colors', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};

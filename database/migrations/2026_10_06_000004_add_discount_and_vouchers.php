<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add discount columns to items table
        Schema::table('items', function (Blueprint $table) {
            if (!Schema::hasColumn('items', 'discount_type')) {
                $table->enum('discount_type', ['fixed', 'percent'])->nullable()->after('catalog_pdf_path');
            }
            if (!Schema::hasColumn('items', 'discount_value')) {
                $table->decimal('discount_value', 12, 2)->unsigned()->nullable()->after('discount_type');
            }
            if (!Schema::hasColumn('items', 'discount_price')) {
                $table->decimal('discount_price', 12, 2)->unsigned()->nullable()->after('discount_value');
            }
        });

        // Create vouchers table
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name')->nullable();
            $table->enum('discount_type', ['fixed', 'percent']);
            $table->decimal('discount_value', 12, 2)->unsigned();
            $table->decimal('min_spend', 12, 2)->unsigned()->default(0);
            $table->decimal('max_discount', 12, 2)->unsigned()->nullable();
            $table->integer('quota')->default(null)->nullable();
            $table->integer('used_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();

            $table->index('code');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vouchers');

        Schema::table('items', function (Blueprint $table) {
            if (Schema::hasColumn('items', 'discount_type')) {
                $table->dropColumn('discount_type');
            }
            if (Schema::hasColumn('items', 'discount_value')) {
                $table->dropColumn('discount_value');
            }
            if (Schema::hasColumn('items', 'discount_price')) {
                $table->dropColumn('discount_price');
            }
        });
    }
};
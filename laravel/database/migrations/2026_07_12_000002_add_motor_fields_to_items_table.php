<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            if (!Schema::hasColumn('items', 'year')) {
                $table->unsignedSmallInteger('year')->nullable()->after('slug');
            }
            if (!Schema::hasColumn('items', 'short_description')) {
                $table->text('short_description')->nullable()->after('description');
            }
            if (!Schema::hasColumn('items', 'stock_status')) {
                $table->string('stock_status')->default('ready')->after('stock');
            }
            if (!Schema::hasColumn('items', 'stock_updated_at')) {
                $table->timestamp('stock_updated_at')->nullable()->after('stock_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn(['year', 'short_description', 'stock_status', 'stock_updated_at']);
        });
    }
};

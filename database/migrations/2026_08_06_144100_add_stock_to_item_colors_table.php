<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('item_colors', function (Blueprint $table) {
            if (!Schema::hasColumn('item_colors', 'stock')) {
                $table->integer('stock')->default(0)->after('weight');
            }
            if (!Schema::hasColumn('item_colors', 'stock_updated_at')) {
                $table->timestamp('stock_updated_at')->nullable()->after('stock');
            }
        });
    }

    public function down(): void
    {
        Schema::table('item_colors', function (Blueprint $table) {
            if (Schema::hasColumn('item_colors', 'stock')) {
                $table->dropColumn('stock');
            }
            if (Schema::hasColumn('item_colors', 'stock_updated_at')) {
                $table->dropColumn('stock_updated_at');
            }
        });
    }
};

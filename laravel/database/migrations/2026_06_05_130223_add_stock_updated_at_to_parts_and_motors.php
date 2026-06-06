<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('parts', 'stock_updated_at')) {
            Schema::table('parts', function (Blueprint $table) {
                $table->timestamp('stock_updated_at')->nullable()->after('status');
            });
        }

        if (!Schema::hasColumn('motors', 'stock_updated_at')) {
            Schema::table('motors', function (Blueprint $table) {
                $table->timestamp('stock_updated_at')->nullable()->after('status');
            });
        }

        if (!Schema::hasColumn('part_variants', 'stock_updated_at')) {
            Schema::table('part_variants', function (Blueprint $table) {
                $table->timestamp('stock_updated_at')->nullable()->after('stock');
            });
        }
    }

    public function down(): void
    {
        Schema::table('parts', function (Blueprint $table) {
            if (Schema::hasColumn('parts', 'stock_updated_at')) {
                $table->dropColumn('stock_updated_at');
            }
        });

        Schema::table('motors', function (Blueprint $table) {
            if (Schema::hasColumn('motors', 'stock_updated_at')) {
                $table->dropColumn('stock_updated_at');
            }
        });

        Schema::table('part_variants', function (Blueprint $table) {
            if (Schema::hasColumn('part_variants', 'stock_updated_at')) {
                $table->dropColumn('stock_updated_at');
            }
        });
    }
};

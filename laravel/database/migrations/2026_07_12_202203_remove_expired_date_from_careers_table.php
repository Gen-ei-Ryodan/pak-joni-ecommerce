<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('careers', 'expired_date')) {
            return;
        }

        // Backfill display_end_date with existing expired_date values before dropping
        DB::table('careers')
            ->whereNull('display_end_date')
            ->whereNotNull('expired_date')
            ->update(['display_end_date' => DB::raw('DATE(expired_date)')]);

        Schema::table('careers', function (Blueprint $table) {
            $table->dropColumn('expired_date');
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('careers', 'expired_date')) {
            Schema::table('careers', function (Blueprint $table) {
                $table->dateTime('expired_date')->nullable()->after('publish_date');
            });
        }
    }
};

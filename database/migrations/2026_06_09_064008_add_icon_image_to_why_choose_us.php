<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('why_choose_us', 'icon_image')) {
            Schema::table('why_choose_us', function (Blueprint $table) {
                $table->string('icon_image')->nullable()->after('icon');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('why_choose_us', 'icon_image')) {
            Schema::table('why_choose_us', function (Blueprint $table) {
                $table->dropColumn('icon_image');
            });
        }
    }
};

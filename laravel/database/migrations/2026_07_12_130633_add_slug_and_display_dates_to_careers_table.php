<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('careers', function (Blueprint $table) {
            $table->date('display_start_date')->nullable()->after('expired_date');
            $table->date('display_end_date')->nullable()->after('display_start_date');
        });

        // Generate slugs for existing records that don't have one
        $records = DB::table('careers')->whereNull('slug')->get();
        foreach ($records as $record) {
            $baseSlug = Str::slug($record->title);
            $slug = $baseSlug;
            $counter = 1;
            while (DB::table('careers')->where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }
            DB::table('careers')->where('id', $record->id)->update(['slug' => $slug]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('careers', function (Blueprint $table) {
            $table->dropColumn(['display_start_date', 'display_end_date']);
        });
    }
};

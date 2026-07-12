<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('careers', function (Blueprint $table) {
            if (!Schema::hasColumn('careers', 'slug')) {
                $table->string('slug')->nullable()->unique()->after('title');
            }
            if (!Schema::hasColumn('careers', 'display_start_date')) {
                $table->date('display_start_date')->nullable()->after('publish_date');
            }
            if (!Schema::hasColumn('careers', 'display_end_date')) {
                $table->date('display_end_date')->nullable()->after('display_start_date');
            }
        });

        // Generate slugs for records that don't have one
        $records = DB::table('careers')->whereNull('slug')->get();
        foreach ($records as $record) {
            $baseSlug = Str::slug($record->title);
            $slug = $baseSlug;
            $counter = 1;
            while (DB::table('careers')->where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter++;
            }
            DB::table('careers')
                ->where('id', $record->id)
                ->update(['slug' => $slug]);
        }
    }

    public function down(): void
    {
        $columns = [];
        if (Schema::hasColumn('careers', 'slug')) $columns[] = 'slug';
        if (Schema::hasColumn('careers', 'display_start_date')) $columns[] = 'display_start_date';
        if (Schema::hasColumn('careers', 'display_end_date')) $columns[] = 'display_end_date';

        if (!empty($columns)) {
            Schema::table('careers', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }
};

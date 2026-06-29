<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            // SQLite: rebuild table to make column nullable
            $this->rebuildBannersTable(nullable: true);
            return;
        }

        DB::statement('ALTER TABLE banners MODIFY image_path VARCHAR(255) NULL');
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->rebuildBannersTable(nullable: false);
            return;
        }

        DB::statement('ALTER TABLE banners MODIFY image_path VARCHAR(255) NOT NULL');
    }

    private function rebuildBannersTable(bool $nullable): void
    {
        // Gather all banner columns from the index schema
        $rows = DB::table('banners')->get();

        Schema::dropIfExists('banners_tmp');

        Schema::create('banners_tmp', function (Blueprint $table) use ($nullable) {
            $table->id();
            $table->string('title');
            if ($nullable) {
                $table->string('image_path')->nullable();
            } else {
                $table->string('image_path');
            }
            $table->string('link_url')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->string('type')->default('hero')->index();
            $table->string('subtitle')->nullable();
            $table->string('button_text')->nullable();
            $table->timestamps();
        });

        foreach ($rows as $row) {
            DB::table('banners_tmp')->insert((array) $row);
        }

        Schema::dropIfExists('banners');
        Schema::rename('banners_tmp', 'banners');
    }
};

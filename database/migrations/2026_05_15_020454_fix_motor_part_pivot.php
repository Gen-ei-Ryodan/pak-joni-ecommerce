<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        if (Schema::hasColumn('motor_part', 'id')) {
            DB::statement('ALTER TABLE motor_part MODIFY id BIGINT UNSIGNED NOT NULL');
            DB::statement('ALTER TABLE motor_part DROP PRIMARY KEY');
            DB::statement('ALTER TABLE motor_part DROP COLUMN id');
        }

        $indexExists = fn (string $name) => DB::table('information_schema.statistics')
            ->where('table_schema', DB::raw('DATABASE()'))
            ->where('table_name', 'motor_part')
            ->where('index_name', $name)
            ->exists();

        if ($indexExists('motor_part_motor_id_part_id_unique')) {
            DB::statement('ALTER TABLE motor_part ADD INDEX motor_part_motor_id_index (motor_id)');
            DB::statement('ALTER TABLE motor_part DROP INDEX motor_part_motor_id_part_id_unique');
        }

        if (! $indexExists('PRIMARY')) {
            DB::statement('ALTER TABLE motor_part ADD PRIMARY KEY (motor_id, part_id)');
        }

        if ($indexExists('motor_part_motor_id_index') && $indexExists('PRIMARY')) {
            DB::statement('ALTER TABLE motor_part DROP INDEX motor_part_motor_id_index');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE motor_part DROP PRIMARY KEY');
        DB::statement('ALTER TABLE motor_part ADD id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY FIRST');
    }
};

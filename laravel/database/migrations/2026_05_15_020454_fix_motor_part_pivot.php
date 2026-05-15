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

        DB::statement('ALTER TABLE motor_part MODIFY id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE motor_part DROP PRIMARY KEY');
        DB::statement('ALTER TABLE motor_part DROP COLUMN id');
        DB::statement('ALTER TABLE motor_part ADD PRIMARY KEY (motor_id, part_id)');
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

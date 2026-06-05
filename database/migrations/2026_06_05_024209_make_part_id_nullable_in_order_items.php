<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE order_items DROP FOREIGN KEY order_items_part_id_foreign');
        DB::statement('ALTER TABLE order_items MODIFY part_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE order_items ADD CONSTRAINT order_items_part_id_foreign FOREIGN KEY (part_id) REFERENCES parts(id) ON DELETE SET NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE order_items DROP FOREIGN KEY order_items_part_id_foreign');
        DB::statement('ALTER TABLE order_items MODIFY part_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE order_items ADD CONSTRAINT order_items_part_id_foreign FOREIGN KEY (part_id) REFERENCES parts(id) ON DELETE RESTRICT');
    }
};

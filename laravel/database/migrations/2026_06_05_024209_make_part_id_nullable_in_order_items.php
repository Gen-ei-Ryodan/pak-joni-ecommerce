<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            // SQLite doesn't support DROP FOREIGN KEY — rebuild table instead
            DB::statement('CREATE TABLE order_items_new (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                order_id INTEGER NOT NULL,
                part_id INTEGER NULL,
                part_variant_id INTEGER NULL,
                sku VARCHAR NOT NULL,
                name VARCHAR NOT NULL,
                variant_name VARCHAR NULL,
                price NUMERIC DEFAULT 0 NOT NULL,
                quantity INTEGER DEFAULT 1 NOT NULL,
                line_total NUMERIC DEFAULT 0 NOT NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL,
                FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
                FOREIGN KEY (part_id) REFERENCES parts(id) ON DELETE SET NULL,
                FOREIGN KEY (part_variant_id) REFERENCES part_variants(id) ON DELETE SET NULL
            )');
            DB::statement('INSERT INTO order_items_new SELECT id, order_id, part_id, part_variant_id, sku, name, variant_name, price, quantity, line_total, created_at, updated_at FROM order_items');
            DB::statement('DROP TABLE order_items');
            DB::statement('ALTER TABLE order_items_new RENAME TO order_items');
            return;
        }

        DB::statement('ALTER TABLE order_items DROP FOREIGN KEY order_items_part_id_foreign');
        DB::statement('ALTER TABLE order_items MODIFY part_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE order_items ADD CONSTRAINT order_items_part_id_foreign FOREIGN KEY (part_id) REFERENCES parts(id) ON DELETE SET NULL');
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement('ALTER TABLE order_items DROP FOREIGN KEY order_items_part_id_foreign');
        DB::statement('ALTER TABLE order_items MODIFY part_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE order_items ADD CONSTRAINT order_items_part_id_foreign FOREIGN KEY (part_id) REFERENCES parts(id) ON DELETE RESTRICT');
    }
};

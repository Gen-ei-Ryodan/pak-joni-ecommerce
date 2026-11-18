<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'is_admin_verified')) {
                $table->boolean('is_admin_verified')->default(false)->after('status')->index();
            }
            if (!Schema::hasColumn('orders', 'admin_verified_at')) {
                $table->timestamp('admin_verified_at')->nullable()->after('is_admin_verified');
            }
            if (!Schema::hasColumn('orders', 'admin_verified_by')) {
                $table->foreignId('admin_verified_by')->nullable()->constrained('users')->nullOnDelete()->after('admin_verified_at');
            }
            if (!Schema::hasColumn('orders', 'spk_order_date')) {
                $table->date('spk_order_date')->nullable()->after('spk_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'admin_verified_by')) {
                $table->dropForeign(['admin_verified_by']);
                $table->dropColumn('admin_verified_by');
            }
            $columnsToDrop = [];
            if (Schema::hasColumn('orders', 'is_admin_verified')) {
                $columnsToDrop[] = 'is_admin_verified';
            }
            if (Schema::hasColumn('orders', 'admin_verified_at')) {
                $columnsToDrop[] = 'admin_verified_at';
            }
            if (Schema::hasColumn('orders', 'spk_order_date')) {
                $columnsToDrop[] = 'spk_order_date';
            }
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};

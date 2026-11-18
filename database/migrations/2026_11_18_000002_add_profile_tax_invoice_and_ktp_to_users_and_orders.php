<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->after('email');
            }
            if (! Schema::hasColumn('users', 'ktp_no')) {
                $table->string('ktp_no')->nullable()->after('phone');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'needs_tax_invoice')) {
                $table->boolean('needs_tax_invoice')->default(false)->after('purchase_type');
            }
            if (! Schema::hasColumn('orders', 'ktp_image_path')) {
                $table->string('ktp_image_path')->nullable()->after('stnk_address');
            }
            if (! Schema::hasColumn('orders', 'buyer_phone')) {
                $table->string('buyer_phone')->nullable()->after('buyer_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'ktp_no']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['needs_tax_invoice', 'ktp_image_path', 'buyer_phone']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('invoice_no')->nullable()->unique();
            $table->date('invoice_date')->nullable();
            $table->string('chassis_no')->nullable();
            $table->string('engine_no')->nullable();
            $table->string('buyer_nik')->nullable();
            $table->string('buyer_name')->nullable();
            $table->text('buyer_address')->nullable();
            $table->text('notes')->nullable();
            $table->string('signatory_name')->nullable()->default('JONI');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['invoice_no']);
            $table->dropColumn([
                'invoice_no', 'invoice_date', 'chassis_no', 'engine_no',
                'buyer_nik', 'buyer_name', 'buyer_address', 'notes', 'signatory_name',
            ]);
        });
    }
};

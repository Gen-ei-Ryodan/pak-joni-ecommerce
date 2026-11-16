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
            $table->string('spk_no')->nullable()->unique()->after('receipt_payment_for');
            $table->date('spk_date')->nullable()->after('spk_no');
            $table->string('stnk_name')->nullable()->after('spk_date');
            $table->text('stnk_address')->nullable()->after('stnk_name');
            $table->string('motor_color')->nullable()->after('stnk_address');
            $table->string('manufacturing_year')->nullable()->after('motor_color');
            $table->string('spk_payment_type')->nullable()->default('cash')->after('manufacturing_year'); // cash, credit
            $table->string('leasing_provider')->nullable()->after('spk_payment_type'); // e.g. Pegadaian, Adira
            $table->decimal('spk_dp_amount', 12, 2)->nullable()->after('leasing_provider');
            $table->string('credit_tenor')->nullable()->after('spk_dp_amount'); // e.g. 12x / 12 bulan
            $table->decimal('monthly_installment', 12, 2)->nullable()->after('credit_tenor');
            $table->decimal('booking_fee', 12, 2)->nullable()->after('monthly_installment'); // uang tanda jadi
            $table->json('spk_checklist_docs')->nullable()->after('booking_fee'); // array of checked docs
            $table->text('spk_notes')->nullable()->after('spk_checklist_docs');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'spk_no',
                'spk_date',
                'stnk_name',
                'stnk_address',
                'motor_color',
                'manufacturing_year',
                'spk_payment_type',
                'leasing_provider',
                'spk_dp_amount',
                'credit_tenor',
                'monthly_installment',
                'booking_fee',
                'spk_checklist_docs',
                'spk_notes',
            ]);
        });
    }
};

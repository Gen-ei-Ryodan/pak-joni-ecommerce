<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pengajuan pembelian kredit (satu pengajuan per order).
        // Menyimpan tahapan/timeline proses kredit beserta timestamp tiap tahapan.
        Schema::create('credit_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('application_no')->unique();
            $table->string('leasing_name')->nullable();
            $table->string('po_number')->nullable();
            $table->date('po_date')->nullable();
            $table->decimal('dp_amount', 12, 2)->default(0); // nominal DP dari leasing (PO)
            $table->string('stage')->default('spk')->index(); // tahapan yang sedang berjalan
            $table->string('status')->default('in_progress')->index(); // in_progress | completed | rejected
            $table->timestamp('spk_at')->nullable();
            $table->timestamp('survey_at')->nullable();
            $table->timestamp('survey_approved_at')->nullable();
            $table->timestamp('unit_preparation_at')->nullable();
            $table->timestamp('payment_at')->nullable();
            $table->timestamp('unit_pickup_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Pembayaran kredit — DP (customer) dan pelunasan (leasing) dicatat terpisah.
        Schema::create('credit_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('credit_application_id')->constrained('credit_applications')->cascadeOnDelete();
            $table->string('type')->index(); // dp | pelunasan
            $table->string('payer_type')->index(); // customer | leasing
            $table->decimal('amount', 12, 2);
            $table->timestamp('paid_at')->nullable();
            $table->string('method')->nullable(); // cash | transfer | qris | other
            $table->string('reference')->nullable(); // no. transfer / bukti
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Satu kali DP dan satu kali pelunasan per pengajuan kredit.
            $table->unique(['credit_application_id', 'type']);
        });

        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'purchase_type')) {
                $table->string('purchase_type', 16)->default('cash')->index()->after('payment_method');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'purchase_type')) {
                $table->dropColumn('purchase_type');
            }
        });

        Schema::dropIfExists('credit_payments');
        Schema::dropIfExists('credit_applications');
    }
};

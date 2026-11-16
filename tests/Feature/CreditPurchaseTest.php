<?php

namespace Tests\Feature;

use App\Exceptions\CreditException;
use App\Models\CategoryType;
use App\Models\CreditPayment;
use App\Models\Item;
use App\Models\ItemColor;
use App\Models\Order;
use App\Models\Part;
use App\Models\PartCategory;
use App\Models\PartVariant;
use App\Models\User;
use App\Services\CreditService;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CreditPurchaseTest extends TestCase
{
    use RefreshDatabase;

    private function buyer(): User
    {
        return User::factory()->create(['role' => 'buyer']);
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function createVehicle(int $stock = 5): ItemColor
    {
        $suffix = uniqid();

        $type = CategoryType::create([
            'name' => 'Motor',
            'slug' => 'motor-'.$suffix,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $item = Item::create([
            'category_type_id' => $type->id,
            'name' => 'SM Sport GT 250',
            'slug' => 'sm-sport-gt-250-'.$suffix,
            'price' => 50000000,
            'status' => 'active',
            'is_active' => true,
        ]);

        return ItemColor::create([
            'item_id' => $item->id,
            'name' => 'Hitam',
            'stock' => $stock,
        ]);
    }

    private function createPartVariant(int $stock = 10): PartVariant
    {
        $cat = PartCategory::create([
            'group' => 'part',
            'name' => 'ECU',
            'slug' => 'ecu',
            'sort_order' => 0,
        ]);

        $part = Part::create([
            'sku' => 'P-ECU-001',
            'name' => 'ECU Racing',
            'slug' => 'ecu-racing',
            'part_category_id' => $cat->id,
            'base_price' => 1000000,
            'status' => 'active',
        ]);

        return PartVariant::create([
            'part_id' => $part->id,
            'sku' => 'P-ECU-001-V1',
            'name' => 'V1',
            'price' => 1200000,
            'stock' => $stock,
            'is_default' => true,
        ]);
    }

    /** Checkout kredit (ambil di dealer) sampai order + pengajuan kredit terbentuk. */
    private function placeCreditOrder(User $user, int $stock = 5): Order
    {
        $color = $this->createVehicle($stock);

        $this->actingAs($user)
            ->post('/cart/items', [
                'itemable_type' => 'item_color',
                'itemable_id' => $color->id,
                'quantity' => 1,
            ])
            ->assertRedirect('/cart');

        $this->actingAs($user)
            ->post('/checkout/address', ['address_id' => 0])
            ->assertRedirect('/checkout/payment');

        $this->actingAs($user)
            ->post('/checkout/place', ['payment_method' => 'credit'])
            ->assertRedirect();

        $order = Order::query()->latest('id')->firstOrFail();

        $this->assertSame('credit', $order->purchase_type);

        return $order;
    }

    /** Majukan seluruh tahapan sampai siap mencatat pembayaran. */
    private function advanceToPayment(CreditService $service, Order $order): void
    {
        $app = $order->creditApplication;

        $service->issueSpk($app, 'SPK terbit.');
        $service->recordSurvey($app, 'Survey oleh leasing.');
        $service->approveSurvey($app, 'Survey disetujui.');
        $service->recordPo($app, [
            'leasing_name' => 'BFI Finance',
            'po_number' => 'PO-BFI-001',
            'po_date' => now()->toDateString(),
            'dp_amount' => 10000000,
        ]);
        $service->prepareUnit($app, 'Unit disiapkan.');
    }

    public function test_buyer_can_place_credit_order_and_application_is_created(): void
    {
        $user = $this->buyer();
        $color = $this->createVehicle();

        $this->actingAs($user)
            ->post('/cart/items', [
                'itemable_type' => 'item_color',
                'itemable_id' => $color->id,
                'quantity' => 1,
            ])
            ->assertRedirect('/cart');

        $this->actingAs($user)
            ->post('/checkout/address', ['address_id' => 0])
            ->assertRedirect('/checkout/payment');

        $this->actingAs($user)
            ->get('/checkout/payment')
            ->assertOk()
            ->assertSee('Kredit (Leasing)');

        $order = $this->placeCreditOrder($user);

        $this->assertSame(Order::PURCHASE_TYPE_CREDIT, $order->purchase_type);
        $this->assertSame('credit', $order->payment_method);
        $this->assertSame('unpaid', $order->status);
        $this->assertSame('pending', $order->payment_status);
        $this->assertFalse($order->is_indent);
        $this->assertSame(0.0, (float) $order->dp_amount);

        $app = $order->creditApplication;
        $this->assertNotNull($app);
        $this->assertSame('spk', $app->stage);
        $this->assertSame('in_progress', $app->status);
        $this->assertStringStartsWith('KRT', $app->application_no);

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'provider' => 'credit',
            'status' => 'pending',
        ]);

        $this->actingAs($user)
            ->get('/checkout/finish/'.$order->id)
            ->assertOk()
            ->assertSee('Pengajuan Kredit Diterima')
            ->assertDontSee('Scan QRIS');
    }

    public function test_credit_option_not_available_for_parts_only_cart(): void
    {
        $user = $this->buyer();
        $variant = $this->createPartVariant();

        $this->actingAs($user)
            ->post('/cart/items', [
                'itemable_type' => 'part_variant',
                'itemable_id' => $variant->id,
                'quantity' => 1,
            ])
            ->assertRedirect('/cart');

        $this->actingAs($user)
            ->post('/checkout/address', ['address_id' => 0])
            ->assertRedirect('/checkout/payment');

        $this->actingAs($user)
            ->get('/checkout/payment')
            ->assertOk()
            ->assertDontSee('Kredit (Leasing)');

        $this->actingAs($user)
            ->post('/checkout/place', ['payment_method' => 'credit'])
            ->assertSessionHasErrors('payment_method');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_credit_flow_records_dp_and_pelunasan_separately_and_decreases_stock(): void
    {
        $user = $this->buyer();
        $admin = $this->admin();
        $order = $this->placeCreditOrder($user, 5);

        $service = app(CreditService::class);
        $this->advanceToPayment($service, $order);

        $app = $order->creditApplication()->first();
        $this->assertSame('payment', $app->currentStage());

        // DP dibayarkan customer langsung ke dealer.
        $service->recordDpPayment($app, [
            'amount' => 10000000,
            'paid_at' => now(),
            'method' => 'transfer',
            'reference' => 'TRF-DP-001',
        ], $admin);

        $order->refresh();
        $this->assertSame('paid', $order->status);
        $this->assertSame('paid', $order->payment_status);
        $this->assertSame('credit', $order->payment_method);

        // Stok berkurang saat order berstatus paid.
        $color = $order->items()->first()->itemable;
        $this->assertSame(4, (int) $color->fresh()->stock);

        $this->assertDatabaseHas('credit_payments', [
            'credit_application_id' => $app->id,
            'type' => CreditPayment::TYPE_DP,
            'payer_type' => CreditPayment::PAYER_CUSTOMER,
            'amount' => 10000000,
            'reference' => 'TRF-DP-001',
            'recorded_by' => $admin->id,
        ]);

        // Pelunasan dibayarkan leasing ke dealer — dicatat terpisah.
        $service->recordPelunasanPayment($app, [
            'amount' => 40000000,
            'paid_at' => now(),
            'method' => 'transfer',
            'reference' => 'TRF-LEASE-001',
        ], $admin);

        $this->assertDatabaseHas('credit_payments', [
            'credit_application_id' => $app->id,
            'type' => CreditPayment::TYPE_PELUNASAN,
            'payer_type' => CreditPayment::PAYER_LEASING,
            'amount' => 40000000,
        ]);

        $app->refresh();
        $this->assertNotNull($app->payment_at);
        $this->assertSame('unit_pickup', $app->stage);
        $this->assertSame(50000000.0, $app->totalPaid());

        // DP dan pelunasan wajib tercatat masing-masing satu kali.
        try {
            $service->recordDpPayment($app, ['amount' => 1000, 'paid_at' => now()], $admin);
            $this->fail('DP kedua seharusnya ditolak.');
        } catch (CreditException $e) {
            $this->assertStringContainsString('sudah tercatat', $e->getMessage());
        }
    }

    public function test_credit_stages_cannot_be_skipped(): void
    {
        $user = $this->buyer();
        $order = $this->placeCreditOrder($user);
        $service = app(CreditService::class);
        $app = $order->creditApplication;

        try {
            $service->recordSurvey($app);
            $this->fail('Melompati tahap SPK seharusnya ditolak.');
        } catch (CreditException $e) {
            $this->assertStringContainsString('SPK', $e->getMessage());
        }

        $service->issueSpk($app);

        // PO belum boleh dicatat sebelum persetujuan survey.
        try {
            $service->recordPo($app, [
                'leasing_name' => 'BFI Finance',
                'po_number' => 'PO-1',
                'po_date' => now()->toDateString(),
                'dp_amount' => 1000000,
            ]);
            $this->fail('PO sebelum persetujuan survey seharusnya ditolak.');
        } catch (CreditException $e) {
            $this->assertStringContainsString('Persetujuan survey', $e->getMessage());
        }

        // DP belum boleh dicatat sebelum tahap persiapan unit.
        $service->recordSurvey($app);
        $service->approveSurvey($app);

        try {
            $service->recordDpPayment($app, ['amount' => 1000000, 'paid_at' => now()]);
            $this->fail('DP sebelum persiapan unit seharusnya ditolak.');
        } catch (CreditException $e) {
            $this->assertStringContainsString('Persiapan Unit', $e->getMessage());
        }
    }

    public function test_rejected_survey_blocks_further_progress(): void
    {
        $user = $this->buyer();
        $order = $this->placeCreditOrder($user);
        $service = app(CreditService::class);
        $app = $order->creditApplication;

        $service->issueSpk($app);
        $service->recordSurvey($app);
        $service->rejectSurvey($app, 'Data penghasilan tidak memadai.');

        $app->refresh();
        $this->assertSame('rejected', $app->status);
        $this->assertStringContainsString('Data penghasilan tidak memadai.', $app->notes);

        try {
            $service->approveSurvey($app);
            $this->fail('Pengajuan yang ditolak seharusnya tidak bisa dilanjutkan.');
        } catch (CreditException $e) {
            $this->assertStringContainsString('Ditolak', $e->getMessage());
        }
    }

    public function test_confirm_pickup_completes_application_and_order(): void
    {
        $user = $this->buyer();
        $admin = $this->admin();
        $order = $this->placeCreditOrder($user, 3);
        $service = app(CreditService::class);
        $orderService = app(OrderService::class);

        $this->advanceToPayment($service, $order);

        $app = $order->creditApplication()->first();
        $service->recordDpPayment($app, ['amount' => 10000000, 'paid_at' => now()], $admin);
        $service->recordPelunasanPayment($app, ['amount' => 40000000, 'paid_at' => now()], $admin);

        $orderService->processOrder($order->fresh());
        $orderService->markAsShipped($order->fresh(), 'dealer_pickup', 'Siap Diambil');

        $service->confirmPickup($app, 'Unit diambil customer.');

        $app->refresh();
        $order->refresh();

        $this->assertSame('completed', $app->status);
        $this->assertNotNull($app->unit_pickup_at);
        $this->assertNull($app->currentStage());
        $this->assertSame('completed', $order->status);
        $this->assertNotNull($order->completed_at);
    }

    public function test_buyer_sees_credit_timeline_on_order_page(): void
    {
        $user = $this->buyer();
        $order = $this->placeCreditOrder($user);
        $service = app(CreditService::class);
        $service->issueSpk($order->creditApplication);

        $this->actingAs($user)
            ->get('/my/orders/'.$order->order_no)
            ->assertOk()
            ->assertSee('Timeline Kredit')
            ->assertSee('Persetujuan Survey')
            ->assertSee('Pengambilan Unit')
            ->assertSee('Menunggu pembayaran DP')
            ->assertDontSee('id="pay-button"', false)
            ->assertDontSee('id="pay-button-sidebar"', false);
    }

    public function test_admin_credit_pages_render(): void
    {
        $user = $this->buyer();
        $order = $this->placeCreditOrder($user);
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get('/admin/credit-applications')
            ->assertOk()
            ->assertSee('Pengajuan Kredit')
            ->assertSee($order->order_no);

        $this->actingAs($admin)
            ->get('/admin/credit-applications/'.$order->creditApplication->id)
            ->assertOk()
            ->assertSee('Timeline Proses Pembelian Kredit')
            ->assertSee('Terbitkan SPK')
            ->assertSee('Buka Pesanan');
    }

    public function test_admin_can_advance_spk_stage_from_view_page(): void
    {
        $user = $this->buyer();
        $order = $this->placeCreditOrder($user);
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(\App\Filament\Resources\CreditApplicationResource\Pages\ViewCreditApplication::class, [
                'record' => $order->creditApplication->id,
            ])
            ->callAction('issue_spk', ['notes' => 'SPK diterbitkan admin.']);

        $order->creditApplication->refresh();
        $this->assertNotNull($order->creditApplication->spk_at);
        $this->assertSame('survey', $order->creditApplication->stage);
        $this->assertStringContainsString('SPK diterbitkan admin.', (string) $order->creditApplication->notes);
    }

    public function test_admin_can_walk_full_credit_timeline_from_view_page(): void
    {
        $user = $this->buyer();
        $admin = $this->admin();
        $order = $this->placeCreditOrder($user, 5);
        $app = $order->creditApplication;

        $page = Livewire::actingAs($admin)
            ->test(\App\Filament\Resources\CreditApplicationResource\Pages\ViewCreditApplication::class, [
                'record' => $app->id,
            ]);

        $page->callAction('issue_spk', ['notes' => null]);
        $page->callAction('record_survey', ['notes' => 'Survey selesai.']);
        $page->callAction('approve_survey', ['notes' => 'Disetujui leasing.']);
        $page->callAction('record_po', [
            'leasing_name' => 'BFI Finance',
            'po_number' => 'PO-BFI-002',
            'po_date' => now()->toDateString(),
            'dp_amount' => 10000000,
        ]);
        $page->callAction('prepare_unit', ['notes' => null]);
        $page->callAction('record_dp', [
            'amount' => 10000000,
            'paid_at' => now()->toDateString(),
            'method' => 'transfer',
            'reference' => 'TRF-1',
        ]);
        $page->callAction('record_pelunasan', [
            'amount' => 40000000,
            'paid_at' => now()->toDateString(),
            'method' => 'transfer',
            'reference' => 'TRF-2',
        ]);
        $page->callAction('confirm_pickup', ['notes' => 'Unit diambil.']);

        $app->refresh();
        $order->refresh();

        $this->assertSame('completed', $app->status);
        $this->assertNull($app->currentStage());
        $this->assertSame('unit_pickup', $app->stage);
        $this->assertSame('BFI Finance', $app->leasing_name);
        $this->assertSame('PO-BFI-002', $app->po_number);
        $this->assertSame(2, $app->payments()->count());
        $this->assertSame(50000000.0, $app->totalPaid());
    }
}

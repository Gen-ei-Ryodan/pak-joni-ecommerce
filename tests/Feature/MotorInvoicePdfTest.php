<?php

namespace Tests\Feature;

use App\Filament\Resources\OrderResource\Pages\EditOrder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MotorInvoicePdfTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrder(User $user, array $invoice = []): Order
    {
        $order = Order::create([
            'user_id' => $user->id,
            'order_no' => 'PJ261109MOTO01',
            'status' => 'paid',
            'payment_status' => 'paid',
            'subtotal' => 21000000,
            'shipping_cost' => 0,
            'shipping_type' => 'dealer_pickup',
            'total' => 21000000,
            'paid_at' => now(),
            'address_snapshot' => [
                'recipient_name' => 'Budi Santoso',
                'address_line1' => 'Jl. Merdeka No. 1',
                'city' => 'Surabaya',
                'province' => 'Jawa Timur',
                'postal_code' => '60111',
            ],
        ] + $invoice);

        OrderItem::create([
            'order_id' => $order->id,
            'sku' => 'MTR-001',
            'name' => 'Honda Vario 160',
            'variant_name' => 'CBS',
            'price' => 21000000,
            'quantity' => 1,
            'line_total' => 21000000,
        ]);

        return $order;
    }

    public function test_invoice_data_autofills_from_order(): void
    {
        $user = User::factory()->create(['role' => 'buyer']);
        $order = $this->makeOrder($user);

        $data = $order->motorInvoiceData();

        $expectedNo = sprintf('%03d/FPSM-T/%d', $order->id, now()->year);
        $this->assertSame($expectedNo, $data['invoice_no']);
        $this->assertSame('Budi Santoso', $data['buyer_name']);
        $this->assertStringContainsString('Jl. Merdeka No. 1', $data['buyer_address']);
        $this->assertSame('JONI', $data['signatory_name']);
        $this->assertSame(now()->toDateString(), $data['invoice_date']);

        $order->update([
            'invoice_no' => 'INV/2026/001',
            'chassis_no' => 'MH1JM8118NK123456',
            'engine_no' => 'JM81E111111',
        ]);

        $data = $order->fresh()->motorInvoiceData();

        $this->assertSame('INV/2026/001', $data['invoice_no']);
        $this->assertSame('MH1JM8118NK123456', $data['chassis_no']);
    }

    public function test_pdf_view_renders_faktur_layout(): void
    {
        $user = User::factory()->create(['role' => 'buyer']);
        $order = $this->makeOrder($user, ['invoice_no' => 'INV/2026/001', 'signatory_name' => 'JONI']);

        $html = view('pdf.motor-invoice', [
            'order' => $order->load('items'),
            'invoice' => $order->motorInvoiceData(),
        ])->render();

        $this->assertStringContainsString('FAKTUR PEMBELIAN SEPEDA MOTOR', $html);
        $this->assertStringContainsString('CV. JOWIMAR MANDIRI MAKMUR', $html);
        $this->assertStringContainsString('Sahabat Bisnis Sejati', $html);
        $this->assertStringContainsString('Nomor Rangka', $html);
        $this->assertStringContainsString('TOTAL KESELURUHAN', $html);
        $this->assertStringContainsString('CATATAN', $html);
        $this->assertStringContainsString('Honda Vario 160', $html);
    }

    public function test_admin_can_download_motor_invoice_pdf(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $order = $this->makeOrder(User::factory()->create(['role' => 'buyer']));

        $response = $this->actingAs($admin)->get(route('admin.orders.motor-invoice', ['order' => $order->id]));

        $response->assertOk();
        $this->assertStringStartsWith('%PDF', (string) $response->getContent());
    }

    public function test_buyer_can_download_only_when_invoice_is_filled_and_admin_verified(): void
    {
        $user = User::factory()->create(['role' => 'buyer']);
        $order = $this->makeOrder($user);

        // Unverified and no invoice_no -> forbidden
        $this->actingAs($user)
            ->get(route('buyer.orders.motor-invoice', ['order' => $order->order_no]))
            ->assertForbidden();

        // Invoice filled but still not verified -> forbidden
        $order->update(['invoice_no' => 'INV/2026/002', 'is_admin_verified' => false]);
        $this->actingAs($user)
            ->get(route('buyer.orders.motor-invoice', ['order' => $order->order_no]))
            ->assertForbidden();

        // Verified by admin -> OK
        $order->update(['is_admin_verified' => true]);
        $response = $this->actingAs($user)
            ->get(route('buyer.orders.motor-invoice', ['order' => $order->order_no]));

        $response->assertOk();
        $this->assertStringStartsWith('%PDF', (string) $response->getContent());
    }

    public function test_buyer_cannot_download_other_buyers_invoice(): void
    {
        $owner = User::factory()->create(['role' => 'buyer']);
        $other = User::factory()->create(['role' => 'buyer']);
        $order = $this->makeOrder($owner, ['invoice_no' => 'INV/2026/003', 'is_admin_verified' => true]);

        $this->actingAs($other)
            ->get(route('buyer.orders.motor-invoice', ['order' => $order->order_no]))
            ->assertForbidden();
    }

    public function test_admin_order_edit_page_has_motor_invoice_section_with_autofill(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $order = $this->makeOrder(User::factory()->create(['role' => 'buyer']));

        $expectedNo = sprintf('%03d/FPSM-T/%d', $order->id, now()->year);

        $component = Livewire::test(EditOrder::class, ['record' => $order->id])
            ->assertSee('Faktur Pembelian Sepeda Motor')
            ->assertSet('data.invoice_no', $expectedNo)
            ->assertSet('data.signatory_name', 'JONI');

        $component
            ->fillForm([
                'invoice_no' => 'INV/2026/010',
                'chassis_no' => 'MH1JM8118NK999999',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $order->refresh();

        $this->assertSame('INV/2026/010', $order->invoice_no);
        $this->assertSame('MH1JM8118NK999999', $order->chassis_no);
        $this->assertSame('Budi Santoso', $order->buyer_name);
    }

    public function test_admin_orders_index_shows_print_action(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->makeOrder($admin);

        $this->actingAs($admin)
            ->get(route('filament.admin.resources.orders.index'))
            ->assertOk()
            ->assertSee('Cetak Faktur Motor');
    }
}

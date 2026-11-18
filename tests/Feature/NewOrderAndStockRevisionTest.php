<?php

namespace Tests\Feature;

use App\Filament\Resources\ItemResource\Pages\ListItemsByType;
use App\Models\Brand;
use App\Models\Category;
use App\Models\CategoryType;
use App\Models\Item;
use App\Models\ItemColor;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\StockMutation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class NewOrderAndStockRevisionTest extends TestCase
{
    use RefreshDatabase;

    private function createItem(string $type = 'motor', int $stock = 5, string $stockStatus = 'ready'): Item
    {
        $categoryType = CategoryType::firstOrCreate(
            ['slug' => $type],
            ['name' => ucfirst($type), 'sort_order' => 1, 'is_active' => true]
        );

        $brand = Brand::firstOrCreate(['slug' => 'test-brand'], ['name' => 'Test Brand']);
        $cat = Category::firstOrCreate(
            ['slug' => 'test-cat-' . $type],
            ['name' => 'Test Category ' . ucfirst($type), 'category_type_id' => $categoryType->id]
        );

        $item = Item::create([
            'category_type_id' => $categoryType->id,
            'brand_id' => $brand->id,
            'category_id' => $cat->id,
            'name' => "Sample {$type}",
            'slug' => "sample-{$type}-" . uniqid(),
            'price' => 20000000,
            'status' => 'active',
            'stock_status' => $stockStatus,
            'stock' => $stock,
            'is_active' => true,
        ]);

        $item->colors()->create([
            'name' => 'Standard',
            'stock' => $stock,
            'color_code' => '#000000',
        ]);

        return $item;
    }

    private function makeOrder(User $buyer, array $overrides = []): Order
    {
        return Order::create(array_merge([
            'user_id' => $buyer->id,
            'order_no' => 'PJ-' . strtoupper(uniqid()),
            'status' => 'paid',
            'payment_status' => 'paid',
            'subtotal' => 20000000,
            'shipping_cost' => 0,
            'shipping_type' => 'dealer_pickup',
            'total' => 20000000,
            'paid_at' => now(),
            'address_snapshot' => [
                'recipient_name' => 'Budi Santoso',
                'address_line1' => 'Jl. Merdeka No. 1',
                'city' => 'Surabaya',
                'province' => 'Jawa Timur',
                'postal_code' => '60111',
            ],
            'is_admin_verified' => true,
        ], $overrides));
    }

    public function test_admin_can_manage_stock_via_interactive_modal(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $item = $this->createItem('motor', 0, 'indent');
        $color = $item->colors->first();

        Livewire::actingAs($admin)
            ->test(ListItemsByType::class)
            ->set('activeCategoryTypeId', (string) $item->category_type_id)
            ->mountTableAction('manage_stock', $item)
            ->setTableActionData([
                "stock_color_{$color->id}" => 10,
                'notes' => 'Restock Gudang',
            ])
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $color->refresh();
        $item->refresh();

        $this->assertSame(10, $color->stock);
        $this->assertSame('ready', $item->stock_status);
        $this->assertSame(10, (int) $item->stock);

        $mutation = StockMutation::where('stockable_type', ItemColor::class)
            ->where('stockable_id', $color->id)
            ->latest('id')
            ->first();
        $this->assertNotNull($mutation);
        $this->assertSame('manual', $mutation->type);
        $this->assertSame(10, $mutation->quantity);
        $this->assertSame(10, $mutation->current_stock);
    }

    public function test_motor_only_order_displays_motor_invoice_and_spk_not_parts_invoice(): void
    {
        $buyer = User::factory()->create(['role' => 'buyer']);
        $item = $this->createItem('motor', 2);
        $color = $item->colors->first();

        $order = $this->makeOrder($buyer, [
            'order_no' => 'PJ-MTR-001',
            'invoice_no' => '001/FPSM-T/2026',
            'chassis_no' => 'MH1TEST123',
            'engine_no' => 'ENGTEST123',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'itemable_type' => ItemColor::class,
            'itemable_id' => $color->id,
            'sku' => 'SKU-MTR-01',
            'name' => $item->name,
            'price' => 20000000,
            'quantity' => 1,
            'line_total' => 20000000,
        ]);

        $this->assertTrue($order->hasVehicle());
        $this->assertFalse($order->hasParts());

        $response = $this->actingAs($buyer)->get(route('buyer.orders.show', ['order' => $order->order_no]));
        $response->assertOk();
        $response->assertSee('Cetak Faktur Motor');
        $response->assertSee('Cetak SPK');
        $response->assertDontSee('Cetak Faktur Sukucadang');
        $response->assertDontSee('Cetak Invoice');
    }

    public function test_parts_only_order_displays_parts_invoice_not_motor_invoice_or_spk(): void
    {
        $buyer = User::factory()->create(['role' => 'buyer']);
        $item = $this->createItem('sparepart', 5);
        $color = $item->colors->first();

        $order = $this->makeOrder($buyer, [
            'order_no' => 'PJ-PRT-001',
            'subtotal' => 150000,
            'total' => 150000,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'itemable_type' => ItemColor::class,
            'itemable_id' => $color->id,
            'sku' => 'SKU-PRT-01',
            'name' => $item->name,
            'price' => 150000,
            'quantity' => 1,
            'line_total' => 150000,
        ]);

        $this->assertFalse($order->hasVehicle());
        $this->assertTrue($order->hasParts());

        $response = $this->actingAs($buyer)->get(route('buyer.orders.show', ['order' => $order->order_no]));
        $response->assertOk();
        $response->assertSee('Cetak Faktur Sukucadang');
        $response->assertDontSee('Cetak Faktur Motor');
        $response->assertDontSee('Cetak SPK');
        $response->assertDontSee('Cetak Invoice');
    }

    public function test_combined_order_displays_all_three_invoices(): void
    {
        $buyer = User::factory()->create(['role' => 'buyer']);
        $motor = $this->createItem('motor', 2);
        $motorColor = $motor->colors->first();

        $part = $this->createItem('sparepart', 5);
        $partColor = $part->colors->first();

        $order = $this->makeOrder($buyer, [
            'order_no' => 'PJ-COMBO-001',
            'subtotal' => 20150000,
            'total' => 20150000,
            'invoice_no' => '002/FPSM-T/2026',
            'chassis_no' => 'MH1TEST789',
            'engine_no' => 'ENGTEST789',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'itemable_type' => ItemColor::class,
            'itemable_id' => $motorColor->id,
            'sku' => 'SKU-MTR-02',
            'name' => $motor->name,
            'price' => 20000000,
            'quantity' => 1,
            'line_total' => 20000000,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'itemable_type' => ItemColor::class,
            'itemable_id' => $partColor->id,
            'sku' => 'SKU-PRT-02',
            'name' => $part->name,
            'price' => 150000,
            'quantity' => 1,
            'line_total' => 150000,
        ]);

        $this->assertTrue($order->hasVehicle());
        $this->assertTrue($order->hasParts());

        $response = $this->actingAs($buyer)->get(route('buyer.orders.show', ['order' => $order->order_no]));
        $response->assertOk();
        $response->assertSee('Cetak Faktur Motor');
        $response->assertSee('Cetak SPK');
        $response->assertSee('Cetak Faktur Sukucadang');
        $response->assertDontSee('Cetak Invoice');
    }

    public function test_unverified_order_blocks_buyer_download_and_shows_verification_warning(): void
    {
        $buyer = User::factory()->create(['role' => 'buyer']);
        $motor = $this->createItem('motor', 2);
        $motorColor = $motor->colors->first();

        $order = $this->makeOrder($buyer, [
            'order_no' => 'PJ-UNVER-001',
            'is_admin_verified' => false,
            'invoice_no' => '003/FPSM-T/2026',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'itemable_type' => ItemColor::class,
            'itemable_id' => $motorColor->id,
            'sku' => 'SKU-MTR-03',
            'name' => $motor->name,
            'price' => 20000000,
            'quantity' => 1,
            'line_total' => 20000000,
        ]);

        // Warning banner shown on order page
        $pageResponse = $this->actingAs($buyer)->get(route('buyer.orders.show', ['order' => $order->order_no]));
        $pageResponse->assertOk();
        $pageResponse->assertSee('sedang dalam proses verifikasi');

        // Direct PDF downloads forbidden (403)
        $this->actingAs($buyer)
            ->get(route('buyer.orders.motor-invoice', ['order' => $order->order_no]))
            ->assertForbidden();

        $this->actingAs($buyer)
            ->get(route('buyer.orders.spk', ['order' => $order->order_no]))
            ->assertForbidden();
    }

    public function test_motor_pdp_allows_indent_order_when_stock_is_zero(): void
    {
        $motor = $this->createItem('motor', 0, 'indent');

        $response = $this->get(route('buyer.motors.show', [
            'categoryType' => $motor->type->slug,
            'slug' => $motor->slug,
        ]));
        $response->assertOk();
        $response->assertSee('Pesan Indent (DP 50%)');
        $this->assertStringNotContainsString('disabled class="w-full inline-flex justify-center', (string) $response->getContent());
    }
}

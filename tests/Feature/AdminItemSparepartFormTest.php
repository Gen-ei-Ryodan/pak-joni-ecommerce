<?php

namespace Tests\Feature;

use App\Filament\Resources\ItemResource\Pages\CreateItem;
use App\Filament\Resources\ItemResource\Pages\ListItemsByType;
use App\Models\Brand;
use App\Models\CategoryType;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminItemSparepartFormTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function seedTypes(): array
    {
        $motor = CategoryType::create(['name' => 'Motor', 'slug' => 'motor', 'sort_order' => 1, 'is_active' => true]);
        $sparepart = CategoryType::create(['name' => 'Sparepart', 'slug' => 'sparepart', 'sort_order' => 2, 'is_active' => true]);

        return [$motor, $sparepart];
    }

    public function test_sparepart_item_admin_list_page_renders(): void
    {
        [$motor, $sparepart] = $this->seedTypes();

        $this->actingAs($this->admin())
            ->get('/admin/items/type/' . $sparepart->id)
            ->assertOk()
            ->assertSee('tableFiltersForm.brand_id')
            ->assertSee('tableFiltersForm.compatibleMotors');
    }

    public function test_motor_list_page_does_not_show_motor_compatibility_filter(): void
    {
        [$motor, $sparepart] = $this->seedTypes();

        $this->actingAs($this->admin())
            ->get('/admin/items/type/' . $motor->id)
            ->assertOk()
            ->assertSee('tableFiltersForm.brand_id')
            ->assertDontSee('tableFiltersForm.compatibleMotors');
    }

    public function test_sparepart_list_exposes_brand_and_motor_filters_with_name_search(): void
    {
        [$motor, $sparepart] = $this->seedTypes();

        // Catatan: Livewire::test selalu mount lewat endpoint uji tanpa {categoryType},
        // jadi tipe di-set sebagai properti publik lalu diperiksa secara live.
        Livewire::test(ListItemsByType::class)
            ->set('activeCategoryTypeId', (string) $sparepart->id)
            ->assertTableFilterVisible('brand_id')
            ->assertTableFilterVisible('compatibleMotors')
            ->assertTableFilterVisible('category_id');

        Livewire::test(ListItemsByType::class)
            ->set('activeCategoryTypeId', (string) $motor->id)
            ->assertTableFilterHidden('compatibleMotors');
    }

    public function test_admin_can_filter_sparepart_list_by_brand(): void
    {
        [$motor, $sparepart] = $this->seedTypes();

        $brandA = Brand::create(['name' => 'Astra Honda', 'slug' => 'astra-honda', 'is_active' => true]);
        $brandB = Brand::create(['name' => 'Yamaha', 'slug' => 'yamaha', 'is_active' => true]);

        $itemA = Item::create([
            'category_type_id' => $sparepart->id,
            'brand_id' => $brandA->id,
            'name' => 'Ban Tubeless 17',
            'slug' => 'ban-tubeless-17',
            'status' => 'active',
            'is_active' => true,
        ]);
        $itemB = Item::create([
            'category_type_id' => $sparepart->id,
            'brand_id' => $brandB->id,
            'name' => 'Oli Mesin 1L',
            'slug' => 'oli-mesin-1l',
            'status' => 'active',
            'is_active' => true,
        ]);

        Livewire::test(ListItemsByType::class)
            ->set('activeCategoryTypeId', (string) $sparepart->id)
            ->filterTable('brand_id', $brandA->id)
            ->assertCanSeeTableRecords([$itemA])
            ->assertCanNotSeeTableRecords([$itemB]);
    }

    public function test_admin_can_filter_sparepart_list_by_motor(): void
    {
        [$motor, $sparepart] = $this->seedTypes();

        $motorA = Item::create([
            'category_type_id' => $motor->id,
            'name' => 'SM Sport GT 250',
            'slug' => 'sm-sport-gt-250',
            'status' => 'active',
            'is_active' => true,
        ]);

        $compatible = Item::create([
            'category_type_id' => $sparepart->id,
            'name' => 'Ban Tubeless 17',
            'slug' => 'ban-tubeless-17',
            'status' => 'active',
            'is_active' => true,
        ]);
        $notCompatible = Item::create([
            'category_type_id' => $sparepart->id,
            'name' => 'Oli Mesin 1L',
            'slug' => 'oli-mesin-1l',
            'status' => 'active',
            'is_active' => true,
        ]);

        $compatible->compatibleMotors()->attach($motorA->id);

        Livewire::test(ListItemsByType::class)
            ->set('activeCategoryTypeId', (string) $sparepart->id)
            ->filterTable('compatibleMotors', [$motorA->id])
            ->assertCanSeeTableRecords([$compatible])
            ->assertCanNotSeeTableRecords([$notCompatible]);
    }

    public function test_admin_can_search_sparepart_list_by_name(): void
    {
        [$motor, $sparepart] = $this->seedTypes();

        $ban = Item::create([
            'category_type_id' => $sparepart->id,
            'name' => 'Ban Tubeless 17',
            'slug' => 'ban-tubeless-17',
            'status' => 'active',
            'is_active' => true,
        ]);
        $oli = Item::create([
            'category_type_id' => $sparepart->id,
            'name' => 'Oli Mesin 1L',
            'slug' => 'oli-mesin-1l',
            'status' => 'active',
            'is_active' => true,
        ]);

        Livewire::test(ListItemsByType::class)
            ->set('activeCategoryTypeId', (string) $sparepart->id)
            ->searchTable('Ban Tubeless')
            ->assertCanSeeTableRecords([$ban])
            ->assertCanNotSeeTableRecords([$oli]);
    }

    public function test_sparepart_item_form_shows_part_number_pdf_and_motor_fields(): void
    {
        [$motor, $sparepart] = $this->seedTypes();

        $this->actingAs($this->admin())
            ->get('/admin/items/create?category_type_id=' . $sparepart->id)
            ->assertOk()
            ->assertSee('Data Sparepart')
            ->assertSee('Part Number')
            ->assertSee('Katalog Part (PDF)')
            ->assertSee('Motor Kompatibel');
    }

    public function test_admin_can_create_sparepart_item_with_part_number_pdf_and_motor(): void
    {
        [$motor, $sparepart] = $this->seedTypes();

        $motorItem = Item::create([
            'category_type_id' => $motor->id,
            'name' => 'SM Sport GT 250',
            'slug' => 'sm-sport-gt-250',
            'status' => 'active',
            'is_active' => true,
        ]);

        Livewire::test(CreateItem::class)
            ->fillForm([
                'category_type_id' => $sparepart->id,
                'name' => 'Ban Tubeless 17',
                'slug' => 'ban-tubeless-17',
                'part_number' => 'PN-BAN-001',
                'catalog_pdf_path' => ['items/catalogs/ban.pdf'],
                'compatibleMotors' => [$motorItem->id],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $item = Item::where('slug', 'ban-tubeless-17')->firstOrFail();
        $this->assertSame('PN-BAN-001', $item->part_number);
        $this->assertSame('items/catalogs/ban.pdf', $item->catalog_pdf_path);
        $this->assertTrue($item->compatibleMotors()->where('motor_id', $motorItem->id)->exists());
    }

    public function test_admin_item_edit_form_can_edit_price(): void
    {
        [$motor, $sparepart] = $this->seedTypes();

        $item = Item::create([
            'category_type_id' => $motor->id,
            'name' => 'PORTER 125',
            'slug' => 'porter-125',
            'price' => 15000000,
            'status' => 'active',
            'is_active' => true,
        ]);

        $this->actingAs($this->admin())
            ->get('/admin/items/' . $item->id . '/edit')
            ->assertOk()
            ->assertSee('Harga')
            ->assertSee('Harga dasar produk');

        Livewire::test(\App\Filament\Resources\ItemResource\Pages\EditItem::class, ['record' => $item->id])
            ->assertFormSet([
                'price' => 15000000,
            ]);
    }

    public function test_sparepart_detail_page_shows_part_number_and_catalog_pdf(): void
    {
        [$motor, $sparepart] = $this->seedTypes();

        $motorItem = Item::create([
            'category_type_id' => $motor->id,
            'name' => 'SM Sport GT 250',
            'slug' => 'sm-sport-gt-250',
            'status' => 'active',
            'is_active' => true,
        ]);

        $item = Item::create([
            'category_type_id' => $sparepart->id,
            'name' => 'Ban Tubeless 17',
            'slug' => 'ban-tubeless-17',
            'part_number' => 'PN-BAN-001',
            'catalog_pdf_path' => 'items/catalogs/ban.pdf',
            'status' => 'active',
            'is_active' => true,
        ]);
        $item->compatibleMotors()->attach($motorItem->id);

        $this->get(route('buyer.motors.show', ['categoryType' => 'sparepart', 'slug' => 'ban-tubeless-17']))
            ->assertOk()
            ->assertSee('Part Number:')
            ->assertSee('PN-BAN-001')
            ->assertSee('Katalog Part (PDF)')
            ->assertSee('Kompatibel Dengan')
            ->assertSee('SM Sport GT 250');
    }
}

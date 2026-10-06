<?php

namespace Tests\Feature;

use App\Filament\Resources\PartResource\Pages\CreatePart;
use App\Models\CategoryType;
use App\Models\Item;
use App\Models\ItemPartCatalog;
use App\Models\Part;
use App\Models\PartCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminPartFormTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function seedMotor(): array
    {
        $type = CategoryType::create([
            'name' => 'Motor',
            'slug' => 'motor',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $partType = CategoryType::create([
            'name' => 'Sparepart',
            'slug' => 'sparepart',
            'sort_order' => 5,
            'is_active' => true,
        ]);

        $item = Item::create([
            'category_type_id' => $type->id,
            'name' => 'SM Sport GT 250',
            'slug' => 'sm-sport-gt-250',
            'price' => 50000000,
            'status' => 'active',
            'is_active' => true,
        ]);

        $category = PartCategory::create([
            'group' => 'part',
            'name' => 'ECU',
            'slug' => 'ecu',
            'category_type_id' => $partType->id,
            'sort_order' => 0,
        ]);

        return [$type, $partType, $item, $category];
    }

    public function test_admin_part_form_contains_part_number_pdf_and_motor_fields(): void
    {
        $this->seedMotor();

        $this->actingAs($this->admin())
            ->get('/admin/parts/create')
            ->assertOk()
            ->assertSee('Part Number')
            ->assertSee('File PDF Katalog Part')
            ->assertSee('Motor & Kendaraan Terkait');
    }

    public function test_admin_part_list_shows_part_number_column(): void
    {
        $this->seedMotor();

        $this->actingAs($this->admin())
            ->get('/admin/parts')
            ->assertOk()
            ->assertSee('Part Number');
    }

    public function test_admin_can_create_part_with_part_number_pdf_and_motor(): void
    {
        [, , $item, $category] = $this->seedMotor();

        Livewire::test(CreatePart::class)
            ->fillForm([
                'category_type_id' => CategoryType::where('slug', 'sparepart')->first()->id,
                'part_category_id' => $category->id,
                'sku' => 'SKU-PN-001',
                'part_number' => 'PN-2026-001',
                'name' => 'Busi Iridium',
                'base_price' => 150000,
                'status' => 'active',
                'stock_status' => 'ready',
                'catalog_pdf_path' => ['parts/catalogs/pn-2026-001.pdf'],
                '_compatible_items_' . $item->category_type_id => [$item->id],
                'variants' => [
                    [
                        'sku' => 'SKU-PN-001-V1',
                        'name' => 'Default',
                        'price' => 150000,
                        'stock' => 5,
                        'weight' => 100,
                        'is_default' => true,
                    ],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $part = Part::where('sku', 'SKU-PN-001')->firstOrFail();

        $this->assertSame('PN-2026-001', $part->part_number);
        $this->assertSame('parts/catalogs/pn-2026-001.pdf', $part->catalog_pdf_path);
        $this->assertTrue($part->items()->where('items.id', $item->id)->exists());
    }

    public function test_admin_edit_part_form_keeps_part_number_and_pdf(): void
    {
        [, , $item, $category] = $this->seedMotor();

        $part = Part::create([
            'category_type_id' => CategoryType::where('slug', 'sparepart')->first()->id,
            'sku' => 'SKU-PN-003',
            'part_number' => 'PN-2026-003',
            'name' => 'Kampas Rem',
            'slug' => 'kampas-rem',
            'part_category_id' => $category->id,
            'catalog_pdf_path' => 'parts/catalogs/pn-2026-003.pdf',
            'base_price' => 175000,
            'status' => 'active',
        ]);
        $part->items()->attach($item->id);

        $this->actingAs($this->admin())
            ->get('/admin/parts/' . $part->id . '/edit')
            ->assertOk()
            ->assertSee('Part Number')
            ->assertSee('File PDF Katalog Part');

        // File harus benar-benar ada di disk agar Filament memuat state PDF-nya.
        \Illuminate\Support\Facades\Storage::disk('public')->put('parts/catalogs/pn-2026-003.pdf', '%PDF-1.4 test');

        try {
            Livewire::test(\App\Filament\Resources\PartResource\Pages\EditPart::class, ['record' => $part->id])
                ->assertFormSet([
                    'part_number' => 'PN-2026-003',
                    'catalog_pdf_path' => 'parts/catalogs/pn-2026-003.pdf',
                ]);
        } finally {
            \Illuminate\Support\Facades\Storage::disk('public')->delete('parts/catalogs/pn-2026-003.pdf');
        }
    }

    public function test_part_detail_page_shows_part_number_and_catalog_pdf(): void
    {
        [, , $item, $category] = $this->seedMotor();

        $part = Part::create([
            'category_type_id' => CategoryType::where('slug', 'sparepart')->first()->id,
            'sku' => 'SKU-PN-002',
            'part_number' => 'PN-2026-002',
            'name' => 'Rantai Part',
            'slug' => 'rantai-part',
            'part_category_id' => $category->id,
            'catalog_pdf_path' => 'parts/catalogs/pn-2026-002.pdf',
            'base_price' => 250000,
            'status' => 'active',
        ]);

        $part->items()->attach($item->id);

        ItemPartCatalog::create([
            'item_id' => $item->id,
            'name' => 'Katalog Part SM Sport GT 250',
            'pdf_path' => 'items/part-catalogs/gt250.pdf',
            'is_active' => true,
            'sort_order' => 0,
        ]);

        $this->get(route('buyer.parts.show', $part->slug))
            ->assertOk()
            ->assertSee('Part Number: PN-2026-002')
            ->assertSee('Katalog Part (PDF)')
            ->assertSee('Katalog Part SM Sport GT 250');
    }
}

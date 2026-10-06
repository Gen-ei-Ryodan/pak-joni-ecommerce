<?php

namespace Tests\Feature;

use App\Filament\Resources\ItemResource\Pages\CreateItem;
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
            ->assertOk();
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

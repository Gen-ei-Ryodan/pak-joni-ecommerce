<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\CategoryType;
use App\Models\ItemPartCatalog;
use App\Models\ItemPartCatalogImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartCatalogGalleryTest extends TestCase
{
    use RefreshDatabase;

    private function categoryType(): CategoryType
    {
        return CategoryType::create([
            'name' => 'Sparepart',
            'slug' => 'sparepart',
            'sort_order' => 1,
            'is_active' => true,
        ]);
    }

    private function brandWithCatalog(): array
    {
        $brand = Brand::create([
            'name' => 'WMOTO',
            'slug' => 'wmoto',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $catalog = ItemPartCatalog::create([
            'brand_id' => $brand->id,
            'name' => 'Neon',
            'slug' => 'neon',
            'pdf_path' => 'items/catalogs/parts-catalog-neon.pdf',
            'image_path' => 'items/part-catalogs/pages/neon/parts-catalog-neon_page-0001.jpg',
            'is_active' => true,
            'sort_order' => 0,
        ]);

        foreach ([1, 2] as $page) {
            ItemPartCatalogImage::create([
                'item_part_catalog_id' => $catalog->id,
                'path' => 'items/part-catalogs/pages/neon/parts-catalog-neon_page-000'.$page.'.jpg',
                'sort_order' => $page - 1,
            ]);
        }

        return [$brand, $catalog];
    }

    public function test_sparepart_choose_page_lists_brands_with_part_catalog(): void
    {
        $this->categoryType();
        $this->brandWithCatalog();

        $this->get(route('buyer.product.choose', ['categoryType' => 'sparepart']))
            ->assertOk()
            ->assertSee('WMOTO')
            ->assertSee('1 katalog');
    }

    public function test_brand_page_lists_models_with_gallery_link(): void
    {
        $this->categoryType();
        [$brand, $catalog] = $this->brandWithCatalog();

        $this->get(route('buyer.product.brand-pdf', [
            'categoryType' => 'sparepart',
            'brand' => $brand->slug,
        ]))
            ->assertOk()
            ->assertSee('Neon')
            ->assertSee('2 halaman')
            ->assertSee(route('buyer.product.brand-pdf.gallery', [
                'categoryType' => 'sparepart',
                'brand' => $brand->slug,
                'catalog' => $catalog->slug,
            ]));
    }

    public function test_gallery_page_shows_all_catalog_images(): void
    {
        $this->categoryType();
        [$brand, $catalog] = $this->brandWithCatalog();

        $this->get(route('buyer.product.brand-pdf.gallery', [
            'categoryType' => 'sparepart',
            'brand' => $brand->slug,
            'catalog' => $catalog->slug,
        ]))
            ->assertOk()
            ->assertSee('Katalog Neon')
            ->assertSee('2 halaman')
            // Galeri berupa grid kotak kecil + lightbox untuk memperbesar.
            ->assertSee('catalog-grid')
            ->assertSee('catalog-thumb', false)
            ->assertSee('catalogLightbox')
            ->assertSee('parts-catalog-neon_page-0001.jpg')
            ->assertSee('parts-catalog-neon_page-0002.jpg');
    }

    public function test_gallery_is_scoped_to_the_brand(): void
    {
        $this->categoryType();
        [$brand, $catalog] = $this->brandWithCatalog();

        $otherBrand = Brand::create([
            'name' => 'ZONTES',
            'slug' => 'zontes',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $this->get(route('buyer.product.brand-pdf.gallery', [
            'categoryType' => 'sparepart',
            'brand' => $otherBrand->slug,
            'catalog' => $catalog->slug,
        ]))->assertNotFound();
    }

    public function test_admin_part_catalog_pages_render(): void
    {
        $this->categoryType();
        [$brand, $catalog] = $this->brandWithCatalog();

        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get('/admin/part-catalogs')
            ->assertOk()
            ->assertSee('Neon');

        $this->actingAs($admin)
            ->get('/admin/part-catalogs/create')
            ->assertOk()
            ->assertSee('Slug URL');

        $this->actingAs($admin)
            ->get('/admin/part-catalogs/'.$catalog->id.'/edit')
            ->assertOk()
            ->assertSee('Neon')
            ->assertSee('Slug URL');
    }
}

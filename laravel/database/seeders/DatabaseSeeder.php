<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\Brand;
use App\Models\Career;
use App\Models\Category;
use App\Models\CategoryType;
use App\Models\CompanyProfile;
use App\Models\CsrArticle;
use App\Models\Dealer;
use App\Models\Event;
use App\Models\EventGallery;
use App\Models\InternalActivity;
use App\Models\Item;
use App\Models\ItemColor;
use App\Models\ItemImage;
use App\Models\ItemPriceList;
use App\Models\ItemPartCatalog;
use App\Models\ItemSpecification;
use App\Models\News;
use App\Models\Part;
use App\Models\PartCategory;
use App\Models\PartImage;
use App\Models\PartSpecification;
use App\Models\PartVariant;
use App\Models\User;
use App\Models\WhyChooseUs;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    private array $brandIds = [];
    private array $categoryIds = [];
    private array $partCategoryIds = [];
    private array $itemIds = [];
    private array $allPartIds = [];
    private array $itemPartMap = [];

    private const ALL_IMAGES = [
        'images/seeder/1.jpeg',
        'images/seeder/2.jpeg',
        'images/seeder/3.jpeg',
        'images/seeder/4.jpeg',
        'images/seeder/5.jpeg',
        'images/seeder/6.jpeg',
        'images/seeder/part1.jpeg',
        'images/seeder/part2.jpeg',
        'images/seeder/part3.jpeg',
    ];

    private function pic(int $id): string
    {
        return self::ALL_IMAGES[$id % count(self::ALL_IMAGES)];
    }

    public function run(): void
    {
        if (app()->environment('local', 'development')) {
            \DB::statement('SET FOREIGN_KEY_CHECKS=0');

            $tables = [
                'banners', 'brands', 'careers', 'categories', 'category_types',
                'company_profiles', 'csr_articles', 'dealers', 'events', 'event_galleries',
                'internal_activities', 'internal_activity_galleries',
                'item_360_images', 'item_colors', 'item_images',
                'item_part', 'item_part_catalogs', 'item_price_lists',
                'item_specifications', 'items',
                'news', 'part_360_images', 'part_categories', 'part_images',
                'part_specifications', 'part_variants', 'parts',
                'users', 'why_choose_us',
            ];

            foreach ($tables as $t) {
                \DB::table($t)->truncate();
            }
            \DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        $this->createAdmin();
        $this->createCategoryTypes();
        $this->createBrands();
        $this->createCategories();
        $this->createPartCategories();
        $this->createItemsFromDummy();
        $this->createPartDummies();
        $this->createItemPartRelations();
        $this->createBanners();
        $this->createWhyChooseUs();
        $this->createNews();
        $this->createEvents();
        $this->createCsr();
        $this->createDealers();
        $this->createCareers();
        $this->createInternalActivities();
        $this->createCompanyProfile();
    }

    private function createAdmin(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@jomoto.co.id'],
            [
                'role' => 'admin',
                'name' => 'Admin JOMOTO',
                'password' => Hash::make('password'),
            ]
        );
    }

    // ===== CATEGORY TYPES =====

    private function createCategoryTypes(): void
    {
        $types = [
            ['Motor', 'motor', 'Kategori produk motor', 'heroicon-o-lifebuoy'],
            ['Sparepart', 'sparepart', 'Kategori produk sparepart', 'heroicon-o-cog-6-tooth'],
            ['Mobil', 'mobil', 'Kategori produk mobil', 'heroicon-o-truck'],
            ['ATV', 'atv', 'Kategori produk ATV', 'heroicon-o-rocket-launch'],
        ];

        foreach ($types as $i => [$name, $slug, $desc, $icon]) {
            CategoryType::create([
                'name' => $name,
                'slug' => $slug,
                'description' => $desc,
                'icon' => $icon,
                'sort_order' => $i + 1,
                'is_active' => true,
            ]);
        }
    }

    // ===== BRANDS =====

    private function createBrands(): void
    {
        $brands = [
            ['WMOTO', 'wmoto', 'Brand motor sport premium dengan desain agresif.'],
            ['SM SPORT', 'sm-sport', 'Motor urban stylish dengan performa tinggi.'],
            ['CF MOTO', 'cf-moto', 'Pabrikan global dengan teknologi terkini.'],
            ['ZONTES', 'zontes', 'Motor adventure dan touring premium.'],
            ['KOVE', 'kove', 'Motor adventure dan rally premium.'],
            ['ZEEHO', 'zeeho', 'Motor listrik ramah lingkungan untuk masa depan.'],
        ];

        foreach ($brands as $i => [$name, $slug, $desc]) {
            $brand = Brand::create([
                'name' => $name,
                'slug' => $slug,
                'logo_path' => null,
                'description' => $desc,
                'sort_order' => $i + 1,
                'is_active' => true,
            ]);
            $this->brandIds[$slug] = $brand->id;
        }
    }

    // ===== CATEGORIES (sub-category per CategoryType) =====

    private function createCategories(): void
    {
        $motorCategories = [
            'Cruiser', 'Matic', 'Moped', 'Classic', 'Sport', 'Sport Racing',
            'Naked Bike', 'Touring', 'Adventure', 'Trail', 'Rally',
            'Multi Touring', 'Maxi Scooter', 'Papio', 'EV', 'ATV',
            'Sport ATV', 'TR-G', 'Utility', 'Letbe Series',
        ];

        $motorType = CategoryType::where('slug', 'motor')->first();
        if (!$motorType) return;

        foreach ($motorCategories as $i => $name) {
            $cat = Category::create([
                'category_type_id' => $motorType->id,
                'name' => $name,
                'slug' => Str::slug($name),
                'sort_order' => $i + 1,
                'is_active' => true,
            ]);
            $this->categoryIds[$name] = $cat->id;
        }
    }

    // ===== PART CATEGORIES =====

    private function createPartCategories(): void
    {
        $spType = CategoryType::where('slug', 'sparepart')->first();
        if (!$spType) return;

        $categories = [
            'Permesinan' => ['Busi', 'Filter Oli', 'Kampas Kopling', 'Piston Kit', 'Gasket Set', 'Oli Mesin', 'Oli Gardan'],
            'Body' => ['Cover Body', 'Spakbor', 'Fairing', 'Visor', 'Handle Cover', 'Spion', 'Jok'],
            'Roda dan Suspensi' => ['Shockbreaker', 'Velg', 'Ban Depan', 'Ban Belakang', 'Bearing Roda', 'Disc Brake', 'Kampas Rem'],
            'Casis' => ['Footstep', 'Standar Tengah', 'Swing Arm', 'Handle Bar', 'Triple Clamp', 'Side Stand'],
            'Elektrikal' => ['Lampu', 'Aki', 'Saklar', 'CDI', 'Spul', 'Kabel Body', 'Speedometer', 'Klakson'],
        ];

        foreach ($categories as $group => $names) {
            foreach ($names as $name) {
                $cat = PartCategory::create([
                    'category_type_id' => $spType->id,
                    'group' => $group,
                    'name' => $name,
                    'slug' => Str::slug($group . ' ' . $name),
                    'sort_order' => 0,
                ]);
                $this->partCategoryIds[$group][$name] = $cat->id;
            }
        }
    }

    // ===== ITEMS FROM DUMMY.TXT =====

    private function createItemsFromDummy(): void
    {
        $motorType = CategoryType::where('slug', 'motor')->first();
        if (!$motorType) return;

        $path = base_path('dummy.txt');
        if (!file_exists($path)) return;

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        array_shift($lines); // skip header

        // Group by NAMA to consolidate color variants
        $grouped = [];
        foreach ($lines as $line) {
            $cols = explode("\t", $line);
            if (count($cols) < 6) continue;

            [$brandName, $jenis, $nama, $spek, $harga, $warna] = $cols;

            $brandSlug = Str::slug($brandName);
            $brandId = $this->brandIds[$brandSlug] ?? null;
            $categoryId = $this->categoryIds[$jenis] ?? null;

            $price = strtolower(trim($harga)) === 'hubungi kami' ? null : (int) filter_var($harga, FILTER_SANITIZE_NUMBER_INT);
            $yr = null;
            if (preg_match('/^(19|20)\d{2}$/', trim($spek), $m)) {
                $yr = (int) $m[0];
            }

            // Handle ATV entries - they belong to a different category type
            $itemTypeId = $motorType->id;
            if (in_array($jenis, ['ATV', 'Sport ATV'])) {
                $atvType = CategoryType::where('slug', 'atv')->first();
                if ($atvType) $itemTypeId = $atvType->id;
            }

            $key = $itemTypeId . '|' . Str::slug($nama);
            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'category_type_id' => $itemTypeId,
                    'brand_id' => $brandId,
                    'category_id' => $categoryId,
                    'name' => trim($nama),
                    'slug' => Str::slug(trim($nama)),
                    'year' => $yr,
                    'price' => $price,
                    'description' => '<p>' . trim($nama) . ' — ' . trim($spek) . '</p>',
                    'short_description' => $brandName . ' ' . trim($nama) . ' | ' . trim($spek),
                    'status' => 'active',
                    'is_active' => true,
                    'stock_status' => $price ? 'ready' : 'indent',
                    'colors' => [],
                ];
            }

            $grouped[$key]['colors'][] = trim($warna);
        }

        $counter = 0;
        foreach ($grouped as $data) {
            $colors = $data['colors'];
            unset($data['colors']);

            $data['thumbnail_path'] = $this->pic($counter + 50);
            $item = Item::create($data);

            // Item images
            ItemImage::create([
                'item_id' => $item->id,
                'path' => $this->pic($counter + 50),
                'sort_order' => 0,
            ]);

            // Color variants
            foreach ($colors as $ci => $colorName) {
                ItemColor::create([
                    'item_id' => $item->id,
                    'name' => $colorName,
                    'color_code' => null,
                    'image_path' => $this->pic($counter + 100 + $ci),
                    'weight' => 100000,
                    'sort_order' => $ci,
                ]);
            }

            // Add spec from SPEK CC
            if ($data['year']) {
                ItemSpecification::create([
                    'item_id' => $item->id,
                    'group' => 'Mesin',
                    'key' => 'Tahun',
                    'value' => (string) $data['year'],
                    'sort_order' => 0,
                ]);
            }

            $this->itemIds[] = $item->id;
            $this->itemPartMap[$item->id] = [];
            $counter++;
        }
    }

    // ===== PARTS =====

    private function createPartDummies(): void
    {
        $spType = CategoryType::where('slug', 'sparepart')->first();
        if (!$spType || empty($this->itemIds)) return;

        $motorGroups = ['Permesinan', 'Body', 'Roda dan Suspensi', 'Casis', 'Elektrikal'];

        $templates = [
            'Permesinan' => [
                ['Busi', 'Busi NGK Racing', 'Busi performa tinggi racing series.', 55000],
                ['Filter Oli', 'Filter Oli Racing', 'Filter oli aftermarket kualitas racing.', 75000],
                ['Kampas Kopling', 'Kampas Kopling Racing', 'Kampas kopling anti slip performa tinggi.', 180000],
                ['Piston Kit', 'Piston Kit Forged', 'Piston kit forged high compression.', 350000],
            ],
            'Body' => [
                ['Cover Body', 'Cover Body Depan', 'Cover body depan ABS high quality.', 250000],
                ['Spakbor', 'Spakbor Depan', 'Spakbor depan tahan benturan.', 95000],
                ['Fairing', 'Fairing Samping', 'Fairing samping full set.', 320000],
                ['Spion', 'Spion Foldable', 'Spion lipat universal M10.', 85000],
            ],
            'Roda dan Suspensi' => [
                ['Ban Depan', 'Ban Depan Tubeless', 'Ban tubeless premium grip maksimal.', 550000],
                ['Kampas Rem', 'Kampas Rem Racing', 'Kampas rem semi-metallic performa tinggi.', 125000],
                ['Velg', 'Velg Racing Alloy', 'Velg racing aluminium ringan.', 1800000],
                ['Shockbreaker', 'Shockbreaker Adjustable', 'Shockbreaker adjustable preload.', 650000],
            ],
            'Casis' => [
                ['Footstep', 'Footstep Racing', 'Footstep CNC billet aluminium.', 450000],
                ['Handle Bar', 'Handle Bar Chrome', 'Handle bar chrome 22mm universal.', 250000],
                ['Standar Tengah', 'Standar Tengah HD', 'Standar tengah heavy duty steel.', 350000],
                ['Side Stand', 'Side Stand Chrome', 'Side stand chrome reinforced spring.', 180000],
            ],
            'Elektrikal' => [
                ['Lampu', 'Lampu LED Headlamp', 'Lampu depan LED super terang.', 350000],
                ['Aki', 'Aki Maintenance Free', 'Aki kering MF 12V 7Ah.', 250000],
                ['Saklar', 'Saklar Starter Assy', 'Saklar starter assembly complete.', 180000],
                ['Klakson', 'Klakson Disc', 'Klakson disc 2-tone 130dB.', 75000],
            ],
        ];

        $counter = 0;

        // Only create parts for a few items to keep seeding fast
        $sampleItems = array_slice($this->itemIds, 0, 12);
        foreach ($sampleItems as $itemId) {
            $item = Item::with('brand')->find($itemId);
            if (!$item) continue;
            $itemName = $item->name;

            foreach ($templates as $group => $entries) {
                foreach ($entries as [$catKey, $displayName, $desc, $price]) {
                    $catId = $this->partCategoryIds[$group][$catKey] ?? null;
                    if (!$catId) continue;

                    $fullName = "{$displayName} — {$itemName}";
                    $part = Part::create([
                        'category_type_id' => $spType->id,
                        'sku' => 'MP-' . str_pad($counter + 1, 5, '0', STR_PAD_LEFT),
                        'name' => $fullName,
                        'slug' => Str::slug($fullName) . '-' . ($counter + 1),
                        'part_category_id' => $catId,
                        'thumbnail_path' => $this->pic($counter + 500),
                        'short_description' => "{$desc} Kompatibel untuk {$itemName}.",
                        'description' => "<p>{$desc}</p><p>Kompatibel dengan <strong>{$itemName}</strong>. Garansi JOMOTO 3 bulan.</p>",
                        'specification' => '',
                        'base_price' => $price,
                        'status' => 'active',
                        'stock_status' => 'ready',
                    ]);

                    // Part variants
                    PartVariant::create([
                        'part_id' => $part->id,
                        'sku' => 'VAR-' . str_pad($counter + 1, 5, '0', STR_PAD_LEFT),
                        'name' => 'Standard',
                        'price' => $price,
                        'stock' => rand(5, 200),
                        'weight' => rand(50, 2000),
                        'is_default' => true,
                    ]);

                    $this->allPartIds[] = $part->id;
                    $this->itemPartMap[$itemId][] = $part->id;
                    $counter++;
                }
            }
        }
    }

    private function createItemPartRelations(): void
    {
        foreach ($this->itemPartMap as $itemId => $partIds) {
            $item = Item::find($itemId);
            if ($item) {
                $item->parts()->syncWithoutDetaching($partIds);
            }
        }
    }

    // ===== BANNERS =====

    private function createBanners(): void
    {
        $heroBanners = [
            ['title' => 'JOMOTO 2025', 'subtitle' => 'New Collection', 'button_text' => 'Jelajahi Produk', 'link_url' => '/produk', 'type' => 'hero'],
            ['title' => 'CFMOTO 450SR', 'subtitle' => 'Sport Performance', 'button_text' => 'Lihat Detail', 'link_url' => '#', 'type' => 'hero'],
            ['title' => 'ZEEHO Electric', 'subtitle' => 'EV Future', 'button_text' => 'Selengkapnya', 'link_url' => '/produk?brand=zeeho', 'type' => 'hero'],
        ];

        $promoBanners = [
            ['title' => 'Promo Akhir Tahun', 'subtitle' => 'Diskon Hingga 5 Juta', 'button_text' => 'Lihat Promo', 'link_url' => '#', 'type' => 'promo'],
            ['title' => 'Gratis Service 4x', 'subtitle' => 'Untuk Pembelian Motor Baru', 'button_text' => 'Syarat & Ketentuan', 'link_url' => '#', 'type' => 'promo'],
            ['title' => 'Trade-In Motor Lama', 'subtitle' => 'Tukar Tambah Harga Tinggi', 'button_text' => 'Cek Sekarang', 'link_url' => '#', 'type' => 'promo'],
        ];

        $launchingBanners = [
            ['title' => 'ZONTES 350X Adventure', 'subtitle' => 'Launching Produk Baru', 'button_text' => 'Lihat Detail', 'link_url' => '#', 'type' => 'launching'],
            ['title' => 'ZEEHO Magnet EV', 'subtitle' => 'Coming Soon', 'button_text' => 'Pre-Order', 'link_url' => '#', 'type' => 'launching'],
        ];

        $kegiatanBanners = [
            ['title' => 'JOMOTO Fest 2025', 'subtitle' => 'Gathering Komunitas Motor', 'button_text' => 'Lihat Event', 'link_url' => '#', 'type' => 'kegiatan'],
            ['title' => 'Ride & Camp', 'subtitle' => 'Petualangan 3 Hari 2 Malam', 'button_text' => 'Daftar', 'link_url' => '#', 'type' => 'kegiatan'],
        ];

        foreach ($heroBanners as $i => $b) {
            Banner::create(array_merge($b, ['sort_order' => $i + 1, 'is_active' => true, 'image_path' => $this->pic($i + 300)]));
        }
        foreach ($promoBanners as $i => $b) {
            Banner::create(array_merge($b, ['sort_order' => $i + 1, 'is_active' => true, 'image_path' => $this->pic($i + 350)]));
        }
        foreach ($launchingBanners as $i => $b) {
            Banner::create(array_merge($b, ['sort_order' => $i + 1, 'is_active' => true, 'image_path' => $this->pic($i + 360)]));
        }
        foreach ($kegiatanBanners as $i => $b) {
            Banner::create(array_merge($b, ['sort_order' => $i + 1, 'is_active' => true, 'image_path' => $this->pic($i + 370)]));
        }
    }

    private function createWhyChooseUs(): void
    {
        $reasons = [
            ['Kualitas Premium', 'Semua produk kami menggunakan material terbaik dengan standar kualitas internasional.'],
            ['Garansi Resmi', 'Setiap pembelian dilengkapi garansi resmi pabrikan untuk ketenangan Anda.'],
            ['Layanan Purna Jual', 'Jaringan bengkel resmi siap melayani perawatan dan perbaikan motor Anda.'],
            ['Harga Kompetitif', 'Dapatkan produk berkualitas dengan harga terbaik di kelasnya.'],
            ['Jaringan Luas', 'Dealer resmi tersebar di seluruh Indonesia untuk kemudahan akses.'],
        ];

        foreach ($reasons as $i => [$title, $desc]) {
            WhyChooseUs::create([
                'title' => $title,
                'description' => $desc,
                'icon' => null,
                'sort_order' => $i + 1,
                'is_active' => true,
            ]);
        }
    }

    private function createNews(): void
    {
        $news = [
            ['JOMOTO Buka Dealer Baru di Surabaya', 'JOMOTO resmi membuka dealer flagship terbaru di Surabaya.'],
            ['CFMOTO 450SR Raih Penghargaan Desain', 'CFMOTO 450SR meraih penghargaan desain motor sport terbaik.'],
            ['ZEEHO AE6 Jadi Motor Listrik Terlaris', 'Penjualan ZEEHO AE6 melesat 200% di kuartal pertama.'],
            ['Tips Perawatan Motor Matic', 'Simak tips perawatan motor matic agar tetap prima dan awet.'],
        ];

        foreach ($news as $i => [$title, $content]) {
            News::create([
                'title' => $title,
                'slug' => Str::slug($title),
                'thumbnail_path' => $this->pic($i + 400),
                'content' => "<p>{$content}</p>",
                'publish_date' => now()->subDays($i * 5),
                'is_active' => true,
            ]);
        }
    }

    private function createEvents(): void
    {
        $events = [
            ['JOMOTO Riding Camp 2025', 'Petualangan 3 hari 2 malam bersama komunitas JOMOTO.', now()->addDays(30), 'Jakarta - Bandung'],
            ['Launching ZONTES 350X', 'Acara launching motor adventure terbaru ZONTES 350X.', now()->addDays(14), 'JOMOTO Flagship Store'],
            ['Workshop Safety Riding', 'Pelatihan safety riding gratis untuk pelanggan setia.', now()->addDays(7), 'JOMOTO Training Center'],
        ];

        foreach ($events as $i => [$title, $desc, $date, $loc]) {
            $event = Event::create([
                'title' => $title,
                'slug' => Str::slug($title),
                'thumbnail_path' => $this->pic($i + 420),
                'description' => "<p>{$desc}</p>",
                'location' => $loc,
                'event_date' => $date,
                'is_active' => true,
            ]);
            for ($g = 1; $g <= 2; $g++) {
                EventGallery::create([
                    'event_id' => $event->id,
                    'path' => $this->pic($i * 10 + $g + 430),
                    'sort_order' => $g,
                ]);
            }
        }
    }

    private function createCsr(): void
    {
        $articles = [
            ['JOMOTO Peduli Pendidikan', 'Program beasiswa untuk anak-anak di sekitar dealer JOMOTO.', now()->subDays(10)],
            ['Tanam 1000 Pohon', 'Gerakan penghijauan bersama komunitas motor.', now()->subDays(20)],
        ];

        foreach ($articles as $i => [$title, $desc, $date]) {
            CsrArticle::create([
                'title' => $title,
                'slug' => Str::slug($title),
                'thumbnail_path' => $this->pic($i + 450),
                'content' => "<p>{$desc}</p>",
                'publish_date' => $date,
                'is_active' => true,
            ]);
        }
    }

    private function createDealers(): void
    {
        $dealers = [
            ['JOMOTO Flagship Jakarta', 'Jl. Sudirman No. 123', 'Jakarta', 'DKI Jakarta', '(021) 555-1234'],
            ['JOMOTO Bandung', 'Jl. Asia Afrika No. 45', 'Bandung', 'Jawa Barat', '(022) 555-5678'],
            ['JOMOTO Surabaya', 'Jl. Tunjungan No. 78', 'Surabaya', 'Jawa Timur', '(031) 555-9012'],
            ['JOMOTO Medan', 'Jl. Gatot Subroto No. 90', 'Medan', 'Sumatra Utara', '(061) 555-3456'],
        ];

        foreach ($dealers as $i => [$name, $addr, $city, $prov, $phone]) {
            Dealer::create([
                'name' => $name,
                'address' => $addr,
                'city' => $city,
                'province' => $prov,
                'phone' => $phone,
                'sort_order' => $i + 1,
                'is_active' => true,
            ]);
        }
    }

    private function createCareers(): void
    {
        $jobs = [
            ['Sales Consultant', 'Jakarta', 'Kami mencari sales consultant berpengalaman untuk dealer flagship Jakarta.', now()->addDays(14)],
            ['Teknisi Motor', 'Bandung', 'Dibutuhkan teknisi motor berpengalaman minimal 2 tahun.', now()->addDays(21)],
            ['Digital Marketing', 'Jakarta', 'JOMOTO mencari digital marketing specialist.', now()->addDays(7)],
        ];

        foreach ($jobs as [$title, $loc, $desc, $deadline]) {
            Career::create([
                'title' => $title,
                'slug' => Str::slug($title),
                'location' => $loc,
                'description' => "<p>{$desc}</p>",
                'status' => 'active',
                'publish_date' => now(),
                'is_active' => true,
            ]);
        }
    }

    private function createInternalActivities(): void
    {
        $activities = [
            ['Training Product Knowledge', 'Pelatihan internal untuk sales team.', now()->subDays(5), 'Training Room'],
            ['Team Building 2025', 'Kegiatan team building tahunan di Puncak.', now()->subDays(30), 'Puncak, Bogor'],
            ['Annual Meeting', 'Rapat tahunan evaluasi kinerja.', now()->subDays(45), 'Main Office'],
        ];

        foreach ($activities as $i => [$title, $desc, $date, $loc]) {
            $act = InternalActivity::create([
                'title' => $title,
                'slug' => Str::slug($title),
                'thumbnail_path' => $this->pic($i + 470),
                'content' => "<p>{$desc}</p>",
                'publish_date' => $date,
                'is_active' => true,
            ]);
            for ($g = 1; $g <= 2; $g++) {
                \App\Models\InternalActivityGallery::create([
                    'internal_activity_id' => $act->id,
                    'path' => $this->pic($i * 10 + $g + 480),
                    'sort_order' => $g,
                ]);
            }
        }
    }

    private function createCompanyProfile(): void
    {
        $profiles = [
            ['company_name', 'PT JOMOTO Indonesia'],
            ['company_description', 'JOMOTO adalah distributor resmi motor dan sparepart premium di Indonesia. Berdiri sejak 2020, kami berkomitmen menghadirkan produk berkualitas dengan pelayanan terbaik.'],
            ['address', 'Jl. Industri Raya No. 88, Jakarta Pusat'],
            ['phone', '(021) 555-0000'],
            ['email', 'info@jomoto.co.id'],
            ['whatsapp', '6281234567890'],
            ['facebook', 'https://facebook.com/jomotoid'],
            ['instagram', 'https://instagram.com/jomotoid'],
            ['youtube', 'https://youtube.com/@jomotoid'],
            ['tiktok', 'https://tiktok.com/@jomotoid'],
        ];

        foreach ($profiles as [$key, $value]) {
            CompanyProfile::updateOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}

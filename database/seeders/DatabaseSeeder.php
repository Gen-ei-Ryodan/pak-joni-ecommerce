<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\Career;
use App\Models\CompanyProfile;
use App\Models\CsrArticle;
use App\Models\Dealer;
use App\Models\Event;
use App\Models\EventGallery;
use App\Models\InternalActivity;
use App\Models\News;
use App\Models\Part;
use App\Models\PartCategory;
use App\Models\PartVariant;
use App\Models\User;
use App\Models\WhyChooseUs;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    private const ALL_IMAGES = [
        'images/seeder/1.jpeg', 'images/seeder/2.jpeg', 'images/seeder/3.jpeg',
        'images/seeder/4.jpeg', 'images/seeder/5.jpeg', 'images/seeder/6.jpeg',
        'images/seeder/part1.jpeg', 'images/seeder/part2.jpeg', 'images/seeder/part3.jpeg',
    ];

    private function pic(int $id): string
    {
        return self::ALL_IMAGES[$id % count(self::ALL_IMAGES)];
    }

    public function run(): void
    {
        if (app()->environment('local', 'development')) {
            $this->truncateAll();
        }

        $this->call(DummyItemSeeder::class);

        $this->createAdmin();
        $this->createPartCategories();
        $this->createParts();
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

    private function truncateAll(): void
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0');
        foreach ([
            'banners', 'careers', 'company_profiles', 'csr_articles', 'dealers',
            'events', 'event_galleries', 'internal_activities', 'internal_activity_galleries',
            'item_360_images', 'item_colors', 'item_images', 'item_part',
            'item_part_catalogs', 'item_price_lists', 'item_specifications', 'items',
            'news', 'part_360_images', 'part_categories', 'part_images',
            'part_specifications', 'part_variants', 'parts', 'users', 'why_choose_us',
        ] as $t) {
            \DB::table($t)->truncate();
        }
        \DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    private function createAdmin(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@jomoto.co.id'],
            ['role' => 'admin', 'name' => 'Admin JOMOTO', 'password' => Hash::make('password')]
        );
        $this->command->info('✓ Admin created');
    }

    private function createPartCategories(): void
    {
        $spType = \App\Models\CategoryType::where('slug', 'sparepart')->first();
        if (!$spType) return;

        $groups = [
            'Permesinan' => ['Busi', 'Filter Oli', 'Kampas Kopling', 'Piston Kit', 'Gasket Set', 'Oli Mesin', 'Oli Gardan'],
            'Body' => ['Cover Body', 'Spakbor', 'Fairing', 'Visor', 'Handle Cover', 'Spion', 'Jok'],
            'Roda dan Suspensi' => ['Shockbreaker', 'Velg', 'Ban Depan', 'Ban Belakang', 'Bearing Roda', 'Disc Brake', 'Kampas Rem'],
            'Casis' => ['Footstep', 'Standar Tengah', 'Swing Arm', 'Handle Bar', 'Triple Clamp', 'Side Stand'],
            'Elektrikal' => ['Lampu', 'Aki', 'Saklar', 'CDI', 'Spul', 'Kabel Body', 'Speedometer', 'Klakson'],
        ];

        foreach ($groups as $group => $names) {
            foreach ($names as $name) {
                PartCategory::create([
                    'category_type_id' => $spType->id, 'group' => $group,
                    'name' => $name, 'slug' => Str::slug($group . ' ' . $name), 'sort_order' => 0,
                ]);
            }
        }

        $this->command->info('✓ PartCategories: ' . PartCategory::count());
    }

    private function createParts(): void
    {
        $spType = \App\Models\CategoryType::where('slug', 'sparepart')->first();
        $itemIds = \App\Models\Item::pluck('id')->take(12)->toArray();
        if (!$spType || empty($itemIds)) return;

        $pcMap = [];
        foreach (PartCategory::all() as $pc) {
            $pcMap[$pc->group][$pc->name] = $pc->id;
        }

        $templates = [
            'Permesinan' => [
                ['Busi', 'Busi NGK Racing', 55000],
                ['Filter Oli', 'Filter Oli Racing', 75000],
                ['Kampas Kopling', 'Kampas Kopling Racing', 180000],
                ['Piston Kit', 'Piston Kit Forged', 350000],
            ],
            'Body' => [
                ['Cover Body', 'Cover Body Depan', 250000],
                ['Spakbor', 'Spakbor Depan', 95000],
                ['Fairing', 'Fairing Samping', 320000],
                ['Spion', 'Spion Foldable', 85000],
            ],
            'Roda dan Suspensi' => [
                ['Ban Depan', 'Ban Depan Tubeless', 550000],
                ['Kampas Rem', 'Kampas Rem Racing', 125000],
                ['Velg', 'Velg Racing Alloy', 1800000],
                ['Shockbreaker', 'Shockbreaker Adjustable', 650000],
            ],
            'Casis' => [
                ['Footstep', 'Footstep Racing', 450000],
                ['Handle Bar', 'Handle Bar Chrome', 250000],
                ['Standar Tengah', 'Standar Tengah HD', 350000],
                ['Side Stand', 'Side Stand Chrome', 180000],
            ],
            'Elektrikal' => [
                ['Lampu', 'Lampu LED Headlamp', 350000],
                ['Aki', 'Aki Maintenance Free', 250000],
                ['Saklar', 'Saklar Starter Assy', 180000],
                ['Klakson', 'Klakson Disc', 75000],
            ],
        ];

        $counter = 0;
        $items = \App\Models\Item::whereIn('id', $itemIds)->get();

        foreach ($items as $item) {
            foreach ($templates as $group => $entries) {
                foreach ($entries as [$catKey, $displayName, $price]) {
                    $catId = $pcMap[$group][$catKey] ?? null;
                    if (!$catId) continue;

                    $fullName = "{$displayName} — {$item->name}";
                    $part = Part::create([
                        'category_type_id' => $spType->id,
                        'sku' => 'MP-' . str_pad(++$counter, 5, '0', STR_PAD_LEFT),
                        'name' => $fullName, 'slug' => Str::slug($fullName) . '-' . $counter,
                        'part_category_id' => $catId,
                        'thumbnail_path' => $this->pic($counter + 500),
                        'short_description' => "Kompatibel untuk {$item->name}.",
                        'base_price' => $price, 'status' => 'active', 'stock_status' => 'ready',
                    ]);

                    PartVariant::create([
                        'part_id' => $part->id,
                        'sku' => 'VAR-' . str_pad($counter, 5, '0', STR_PAD_LEFT),
                        'name' => 'Standard', 'price' => $price,
                        'stock' => rand(5, 200), 'weight' => rand(50, 2000), 'is_default' => true,
                    ]);

                    $item->parts()->syncWithoutDetaching($part->id);
                }
            }
        }

        $this->command->info("✓ Parts: {$counter} created");
    }

    private function createBanners(): void
    {
        $data = [
            ['JOMOTO 2025', 'New Collection', 'Jelajahi Produk', '/produk', 'hero'],
            ['CFMOTO 450SR', 'Sport Performance', 'Lihat Detail', '#', 'hero'],
            ['ZEEHO Electric', 'EV Future', 'Selengkapnya', '/produk?brand=zeeho', 'hero'],
            ['Promo Akhir Tahun', 'Diskon Hingga 5 Juta', 'Lihat Promo', '#', 'promo'],
            ['Gratis Service 4x', 'Service Gratis', 'Lihat Promo', '#', 'promo'],
            ['Trade-In Motor Lama', 'Tukar Tambah', 'Cek Sekarang', '#', 'promo'],
            ['ZONTES 350X Adventure', 'Launching', 'Lihat Detail', '#', 'launching'],
            ['JOMOTO Fest 2025', 'Gathering', 'Lihat Event', '#', 'kegiatan'],
        ];

        foreach ($data as $i => $d) {
            Banner::create([
                'title' => $d[0], 'subtitle' => $d[1], 'button_text' => $d[2],
                'link_url' => $d[3], 'type' => $d[4],
                'image_path' => $this->pic($i + 300), 'sort_order' => $i + 1, 'is_active' => true,
            ]);
        }

        $this->command->info('✓ Banners: ' . Banner::count());
    }

    private function createWhyChooseUs(): void
    {
        foreach ([
            ['Kualitas Premium', 'Semua produk menggunakan material terbaik.'],
            ['Garansi Resmi', 'Garansi resmi pabrikan untuk ketenangan Anda.'],
            ['Layanan Purna Jual', 'Jaringan bengkel resmi siap melayani.'],
            ['Harga Kompetitif', 'Produk berkualitas dengan harga terbaik.'],
            ['Jaringan Luas', 'Dealer resmi di seluruh Indonesia.'],
        ] as $i => [$title, $desc]) {
            WhyChooseUs::create([
                'title' => $title, 'description' => $desc,
                'icon' => null, 'sort_order' => $i + 1, 'is_active' => true,
            ]);
        }
    }

    private function createNews(): void
    {
        foreach ([
            ['JOMOTO Buka Dealer Baru di Surabaya', 'JOMOTO membuka dealer flagship terbaru di Surabaya.'],
            ['CFMOTO 450SR Raih Penghargaan Desain', 'CFMOTO 450SR meraih penghargaan desain terbaik.'],
            ['ZEEHO AE6 Jadi Motor Listrik Terlaris', 'Penjualan ZEEHO AE6 melesat 200%.'],
            ['Tips Perawatan Motor Matic', 'Tips agar motor matic tetap prima.'],
        ] as $i => [$title, $content]) {
            News::create([
                'title' => $title, 'slug' => Str::slug($title),
                'thumbnail_path' => $this->pic($i + 400),
                'content' => "<p>{$content}</p>",
                'publish_date' => now()->subDays($i * 5), 'is_active' => true,
            ]);
        }

        $this->command->info('✓ News: ' . \App\Models\News::count());
    }

    private function createEvents(): void
    {
        $events = [
            ['JOMOTO Riding Camp 2025', 'Petualangan 3 hari 2 malam.', now()->addDays(30), 'Jakarta - Bandung'],
            ['Launching ZONTES 350X', 'Launching motor adventure terbaru.', now()->addDays(14), 'JOMOTO Store'],
            ['Workshop Safety Riding', 'Pelatihan safety riding gratis.', now()->addDays(7), 'Training Center'],
        ];

        foreach ($events as $i => [$title, $desc, $date, $loc]) {
            $event = Event::create([
                'title' => $title, 'slug' => Str::slug($title),
                'thumbnail_path' => $this->pic($i + 420),
                'description' => "<p>{$desc}</p>", 'location' => $loc,
                'event_date' => $date, 'is_active' => true,
            ]);
            for ($g = 1; $g <= 2; $g++) {
                EventGallery::create([
                    'event_id' => $event->id,
                    'path' => $this->pic($i * 10 + $g + 430), 'sort_order' => $g,
                ]);
            }
        }

        $this->command->info('✓ Events: ' . Event::count());
    }

    private function createCsr(): void
    {
        foreach ([
            ['JOMOTO Peduli Pendidikan', 'Program beasiswa untuk anak-anak.', now()->subDays(10)],
            ['Tanam 1000 Pohon', 'Gerakan penghijauan bersama komunitas.', now()->subDays(20)],
        ] as $i => [$title, $desc, $date]) {
            CsrArticle::create([
                'title' => $title, 'slug' => Str::slug($title),
                'thumbnail_path' => $this->pic($i + 450),
                'content' => "<p>{$desc}</p>",
                'publish_date' => $date, 'is_active' => true,
            ]);
        }

        $this->command->info('✓ CSR: ' . CsrArticle::count());
    }

    private function createDealers(): void
    {
        foreach ([
            ['JOMOTO Flagship Jakarta', 'Jl. Sudirman No. 123', 'Jakarta', 'DKI Jakarta', '(021) 555-1234'],
            ['JOMOTO Bandung', 'Jl. Asia Afrika No. 45', 'Bandung', 'Jawa Barat', '(022) 555-5678'],
            ['JOMOTO Surabaya', 'Jl. Tunjungan No. 78', 'Surabaya', 'Jawa Timur', '(031) 555-9012'],
            ['JOMOTO Medan', 'Jl. Gatot Subroto No. 90', 'Medan', 'Sumatra Utara', '(061) 555-3456'],
        ] as $i => [$name, $addr, $city, $prov, $phone]) {
            Dealer::create([
                'name' => $name, 'address' => $addr, 'city' => $city,
                'province' => $prov, 'phone' => $phone,
                'sort_order' => $i + 1, 'is_active' => true,
            ]);
        }
    }

    private function createCareers(): void
    {
        foreach ([
            ['Sales Consultant', 'Jakarta', 'Kami mencari sales consultant.'],
            ['Teknisi Motor', 'Bandung', 'Dibutuhkan teknisi motor berpengalaman.'],
            ['Digital Marketing', 'Jakarta', 'Mencari digital marketing specialist.'],
        ] as [$title, $loc, $desc]) {
            Career::create([
                'title' => $title, 'slug' => Str::slug($title),
                'location' => $loc, 'description' => "<p>{$desc}</p>",
                'status' => 'active', 'publish_date' => now(), 'is_active' => true,
            ]);
        }
    }

    private function createInternalActivities(): void
    {
        foreach ([
            ['Training Product Knowledge', 'Pelatihan internal sales team.', 'Training Room'],
            ['Team Building 2025', 'Team building tahunan di Puncak.', 'Puncak, Bogor'],
            ['Annual Meeting', 'Rapat tahunan evaluasi kinerja.', 'Main Office'],
        ] as $i => [$title, $desc, $loc]) {
            $act = InternalActivity::create([
                'title' => $title, 'slug' => Str::slug($title),
                'thumbnail_path' => $this->pic($i + 470),
                'content' => "<p>{$desc}</p>",
                'publish_date' => now()->subDays($i === 0 ? 5 : ($i === 1 ? 30 : 45)),
                'is_active' => true,
            ]);
            for ($g = 1; $g <= 2; $g++) {
                \App\Models\InternalActivityGallery::create([
                    'internal_activity_id' => $act->id,
                    'path' => $this->pic($i * 10 + $g + 480), 'sort_order' => $g,
                ]);
            }
        }
    }

    private function createCompanyProfile(): void
    {
        foreach ([
            ['company_name', 'PT JOMOTO Indonesia'],
            ['company_description', 'JOMOTO distributor resmi motor dan sparepart premium di Indonesia.'],
            ['address', 'Jl. Industri Raya No. 88, Jakarta Pusat'],
            ['phone', '(021) 555-0000'], ['email', 'info@jomoto.co.id'],
            ['whatsapp', '6281234567890'],
            ['facebook', 'https://facebook.com/jomotoid'],
            ['instagram', 'https://instagram.com/jomotoid'],
            ['youtube', 'https://youtube.com/@jomotoid'],
            ['tiktok', 'https://tiktok.com/@jomotoid'],
        ] as [$key, $value]) {
            CompanyProfile::updateOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}

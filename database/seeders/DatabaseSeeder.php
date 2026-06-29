<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\Brand;
use App\Models\Career;
use App\Models\CompanyProfile;
use App\Models\CsrArticle;
use App\Models\Dealer;
use App\Models\Event;
use App\Models\EventGallery;
use App\Models\InternalActivity;
use App\Models\Motor;
use App\Models\MotorCategory;
use App\Models\MotorColor;
use App\Models\MotorImage;
use App\Models\MotorSpecification;
use App\Models\News;
use App\Models\Part;
use App\Models\PartCatalog;
use App\Models\PartCategory;
use App\Models\PartImage;
use App\Models\PartSpecification;
use App\Models\PartVariant;
use App\Models\PriceList;
use App\Models\ProductHighlight;
use App\Models\User;
use App\Models\WhyChooseUs;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    // partCategoryIds[golongan][namaKategori] => id
    private array $partCategoryIds = [];
    private array $motorIds = [];
    private array $allPartIds = [];
    private array $brandIds = [];
    private array $categoryIds = [];
    private array $categoryMap = [];
    // motorPartMap[motorId] => [partId, partId, ...]
    private array $motorPartMap = [];

    // Pool of local seeder images (located in public/images/seeder/)
    private const MOTOR_IMAGES = [
        'images/seeder/1.jpeg',
        'images/seeder/2.jpeg',
        'images/seeder/3.jpeg',
        'images/seeder/4.jpeg',
        'images/seeder/5.jpeg',
        'images/seeder/6.jpeg',
    ];

    private const PART_IMAGES = [
        'images/seeder/part1.jpeg',
        'images/seeder/part2.jpeg',
        'images/seeder/part3.jpeg',
    ];

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

    private function pic(int $w, int $h, int $id): string
    {
        // Use local seeded images cycling through the pool
        $pool = self::ALL_IMAGES;
        return $pool[$id % count($pool)];
    }

    private function motorPic(int $id): string
    {
        return self::MOTOR_IMAGES[$id % count(self::MOTOR_IMAGES)];
    }

    private function partPic(int $id): string
    {
        return self::PART_IMAGES[$id % count(self::PART_IMAGES)];
    }

    public function run(): void
    {
        if (app()->environment('local', 'development')) {
            \DB::statement('SET FOREIGN_KEY_CHECKS=0');
            Brand::truncate();
            MotorCategory::truncate();
            PartCategory::truncate();
            Motor::truncate();
            MotorImage::truncate();
            MotorColor::truncate();
            MotorSpecification::truncate();
            Part::truncate();
            PartVariant::truncate();
            PartSpecification::truncate();
            PartImage::truncate();
            Banner::truncate();
            ProductHighlight::truncate();
            WhyChooseUs::truncate();
            News::truncate();
            Event::truncate();
            EventGallery::truncate();
            CsrArticle::truncate();
            Career::truncate();
            InternalActivity::truncate();
            \App\Models\InternalActivityGallery::truncate();
            Dealer::truncate();
            PriceList::truncate();
            PartCatalog::truncate();
            CompanyProfile::truncate();
            \DB::statement('SET FOREIGN_KEY_CHECKS=1');
            \DB::table('motor_part')->truncate();
        }
        $this->createAdmin();
        $this->createBrands();
        $this->createCategories();
        $this->createPartCategories();
        $this->createMotors();
        $this->createMotorDetails();
        $this->createParts();
        $this->createPartVariants();
        $this->createPartImages();
        $this->createMotorImages();
        $this->attachMotorParts();
        $this->createBanners();
        $this->createWhyChooseUs();
        $this->createProductHighlights();
        $this->createNews();
        $this->createEvents();
        $this->createCsr();
        $this->createDealers();
        $this->createCareers();
        $this->createInternalActivities();
        $this->createPriceLists();
        $this->createPartCatalogs();
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

    private function createBrands(): void
    {
        $brands = [
            ['WMOTO', 'wmoto', 'Brand motor sport premium dengan desain agresif.'],
            ['SM SPORT', 'sm-sport', 'Motor urban stylish dengan performa tinggi.'],
            ['CFMOTO', 'cfmoto', 'Pabrikan global dengan teknologi terkini.'],
            ['ZONTES', 'zontes', 'Motor adventure dan touring premium.'],
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

    private function createCategories(): void
    {
        $map = [
            'Matic' => ['wmoto', 'sm-sport', 'zeeho'],
            'Cruiser' => ['wmoto', 'cfmoto'],
            'Naked Bike' => ['wmoto', 'sm-sport', 'zontes', 'cfmoto'],
            'Sport' => ['wmoto', 'cfmoto', 'zontes'],
            'Touring' => ['cfmoto', 'zontes'],
            'Adventure' => ['cfmoto', 'zontes'],
            'Trail' => ['wmoto', 'sm-sport'],
            'EV' => ['zeeho'],
        ];

        $i = 0;
        foreach ($map as $catName => $brandSlugs) {
            foreach ($brandSlugs as $slug) {
                $brandId = $this->brandIds[$slug] ?? null;
                if (! $brandId) continue;

                $cat = MotorCategory::create([
                    'name' => $catName,
                    'slug' => Str::slug($catName . '-' . $slug),
                    'brand_id' => $brandId,
                    'sort_order' => ++$i,
                ]);
                $this->categoryIds[] = $cat->id;
                $this->categoryMap["{$slug}:{$catName}"] = $cat->id;
            }
        }
    }

    private function createPartCategories(): void
    {
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
                    'group' => $group,
                    'name' => $name,
                    'slug' => Str::slug($group . ' ' . $name),
                    'sort_order' => 0,
                ]);
                $this->partCategoryIds[$group][$name] = $cat->id;
            }
        }
    }

    private function createMotors(): void
    {
        $motors = [
            ['WMOTO Xtreme 250', 'wmoto', 'Matic', 2025, 45900000, 'Sport naked bike 250cc dengan desain agresif dan performa bertenaga.'],
            ['WMOTO Cruiser 400', 'wmoto', 'Cruiser', 2024, 72000000, 'Motor cruiser 400cc bergaya klasik dengan kenyamanan maksimal.'],
            ['SM SPORT Urban 150', 'sm-sport', 'Matic', 2025, 28500000, 'Matic urban sporty 150cc dengan teknologi injeksi terbaru.'],
            ['SM SPORT Neo 250', 'sm-sport', 'Naked Bike', 2024, 42000000, 'Naked bike 250cc bergaya neo-retro dengan fitur modern.'],
            ['CFMOTO 450SR', 'cfmoto', 'Sport', 2025, 79900000, 'Sport bike 450cc dengan fairing aerodinamis dan performa superior.'],
            ['CFMOTO 800MT', 'cfmoto', 'Adventure', 2024, 165000000, 'Adventure touring 800cc siap jelajah jarak jauh.'],
            ['CFMOTO Papio 125', 'cfmoto', 'Sport', 2025, 35000000, 'Mini sport bike 125cc cocok untuk pemula.'],
            ['ZONTES 350T', 'zontes', 'Touring', 2025, 89000000, 'Touring 350cc dengan fitur lengkap dan kenyamanan premium.'],
            ['ZONTES 350X', 'zontes', 'Adventure', 2025, 95000000, 'Adventure 350cc tangguh di segala medan.'],
            ['ZONTES 125U', 'zontes', 'Naked Bike', 2024, 38000000, 'Naked bike 125cc stylish dengan teknologi modern.'],
            ['ZEEHO AE6', 'zeeho', 'Matic', 2025, 28000000, 'Motor listrik matic 1500W untuk mobilitas urban.'],
            ['ZEEHO Magnet', 'zeeho', 'EV', 2024, 19500000, 'Motor listrik compact ideal untuk perjalanan pendek.'],
        ];

        foreach ($motors as $i => [$name, $brand, $catName, $year, $price, $desc]) {
            $motor = Motor::create([
                'brand_id' => $this->brandIds[$brand] ?? null,
                'category_id' => $this->categoryMap["{$brand}:{$catName}"] ?? null,
                'name' => $name,
                'slug' => Str::slug($name),
                'year' => $year,
                'price' => $price,
                'thumbnail_path' => $this->pic(600, 400, $i + 50),
                'short_description' => $desc,
                'description' => '<p>' . $desc . '</p><p>Motor ini dilengkapi dengan mesin berperforma tinggi, desain modern, dan fitur keselamatan terkini. Tersedia dalam beberapa pilihan warna menarik.</p>',
                'status' => 'active',
                'stock_status' => 'ready',
            ]);
            $this->motorIds[] = $motor->id;
            $this->motorPartMap[$motor->id] = [];
        }
    }

    private function createMotorDetails(): void
    {
        $colors = ['Merah', 'Hitam', 'Biru', 'Putih', 'Silver', 'Hijau Army'];
        $colorCodes = ['#dc2626', '#1a1a1a', '#2563eb', '#f5f5f5', '#c0c0c0', '#4d7c0f'];
        $specsData = [
            [['Mesin dan Performa', 'Tipe Mesin', '4 Langkah, SOHC, Pendingin Cairan'], ['Mesin dan Performa', 'Kapasitas', '249cc'], ['Mesin dan Performa', 'Tenaga Maks', '26.5 HP @ 9250 rpm'], ['Mesin dan Performa', 'Torsi Maks', '22.5 Nm @ 7250 rpm'], ['Dimensi', 'Panjang', '2,050 mm'], ['Dimensi', 'Lebar', '780 mm'], ['Dimensi', 'Tinggi', '1,075 mm'], ['Dimensi', 'Berat Kosong', '148 kg'], ['Sasis', 'Rangka', 'Diamond Frame'], ['Sasis', 'Suspensi Depan', 'Telescopic 41mm'], ['Sasis', 'Suspensi Belakang', 'Monoshock Adjustable'], ['Fitur', 'ABS', 'Dual Channel'], ['Fitur', 'Panel Instrumen', 'Full Digital LCD'], ['Fitur', 'Lampu', 'Full LED']],
            [['Mesin dan Performa', 'Tipe Mesin', '4 Langkah, V-Twin, Pendingin Udara'], ['Mesin dan Performa', 'Kapasitas', '396cc'], ['Mesin dan Performa', 'Tenaga Maks', '35 HP @ 8000 rpm'], ['Dimensi', 'Panjang', '2,240 mm'], ['Dimensi', 'Lebar', '920 mm'], ['Dimensi', 'Berat Kosong', '195 kg'], ['Sasis', 'Rangka', 'Double Cradle Steel'], ['Fitur', 'Sistem Injeksi', 'EFI Bosch']],
            [['Mesin dan Performa', 'Tipe Mesin', '4 Langkah, SOHC, Pendingin Cairan'], ['Mesin dan Performa', 'Kapasitas', '149cc'], ['Mesin dan Performa', 'Tenaga Maks', '12 HP @ 8500 rpm'], ['Dimensi', 'Berat Kosong', '112 kg'], ['Sasis', 'Rangka', 'Underbone Steel'], ['Fitur', 'Sistem Kunci', 'Smart Keyless']],
        ];

        // Estimated motorcycle weights in grams (matching $this->motorIds order)
        $motorWeights = [148000, 195000, 112000, 150000, 170000, 230000, 120000, 190000, 180000, 130000, 100000, 85000];

        foreach ($this->motorIds as $i => $motorId) {
            $weight = $motorWeights[$i] ?? 150000;

            for ($c = 0; $c < 3; $c++) {
                MotorColor::create([
                    'motor_id' => $motorId,
                    'name' => $colors[($i + $c) % count($colors)],
                    'color_code' => $colorCodes[($i + $c) % count($colorCodes)],
                    'weight' => $weight,
                    'sort_order' => $c,
                ]);
            }

            $specs = $specsData[$i % count($specsData)];
            foreach ($specs as $j => [$group, $key, $value]) {
                MotorSpecification::create([
                    'motor_id' => $motorId,
                    'group' => $group,
                    'key' => $key,
                    'value' => $value,
                    'sort_order' => $j,
                ]);
            }
        }
    }

    private function createParts(): void
    {
        $motorNames = [
            'WMOTO Xtreme 250', 'WMOTO Cruiser 400',
            'SM SPORT Urban 150', 'SM SPORT Neo 250',
            'CFMOTO 450SR', 'CFMOTO 800MT', 'CFMOTO Papio 125',
            'ZONTES 350T', 'ZONTES 350X', 'ZONTES 125U',
            'ZEEHO AE6', 'ZEEHO Magnet',
        ];

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

        $specsTemplates = [
            'Permesinan' => [
                ['Material dan Kualitas', 'Material', 'Aluminium Alloy / Steel'],
                ['Material dan Kualitas', 'Tipe', 'Aftermarket Performance'],
                ['Material dan Kualitas', 'Bobot', '150 gram'],
                ['Kompatibilitas', 'Kompatibel Dengan', 'Semua motor 150cc - 400cc'],
            ],
            'Body' => [
                ['Material dan Dimensi', 'Material', 'ABS Plastic High Quality'],
                ['Material dan Dimensi', 'Ketebalan', '2.5 mm'],
                ['Material dan Dimensi', 'Warna', 'Hitam / Primer Siap Cat'],
                ['Pemasangan', 'Mounting Type', 'Baut M6 Original'],
            ],
            'Roda dan Suspensi' => [
                ['Material dan Spesifikasi', 'Material', 'Aluminium / Karet Kompon'],
                ['Material dan Spesifikasi', 'Ukuran', '17 inch'],
                ['Material dan Spesifikasi', 'Load Capacity', '150 kg'],
                ['Fitur', 'Adjustable', 'Ya (Preload)'],
            ],
            'Casis' => [
                ['Material dan Dimensi', 'Material', 'Baja Karbon Tinggi / Aluminium'],
                ['Material dan Dimensi', 'Diameter', '22 mm'],
                ['Material dan Dimensi', 'Panjang', '280 mm'],
                ['Finishing', 'Surface Finish', 'Chrome / Anodized Hitam'],
            ],
            'Elektrikal' => [
                ['Spesifikasi Elektrik', 'Voltage', '12V DC'],
                ['Spesifikasi Elektrik', 'Watt / Ampere', '35W / 7Ah'],
                ['Spesifikasi Elektrik', 'Tipe Konektor', 'Universal Plug & Play'],
                ['Performa', 'Output', '130 dB / 3000 Lumen'],
            ],
        ];

        $counter = 0;

        foreach ($this->motorIds as $mIdx => $motorId) {
            $mName = $motorNames[$mIdx] ?? "Motor #{$motorId}";

            foreach ($templates as $group => $items) {
                foreach ($items as [$catKey, $displayName, $desc, $price]) {
                    $fullName = "{$displayName} — {$mName}";
                    $catId = $this->partCategoryIds[$group][$catKey] ?? null;
                    if (! $catId) continue;

                    $part = Part::create([
                        'sku' => 'MP-' . str_pad($counter + 1, 5, '0', STR_PAD_LEFT),
                        'name' => $fullName,
                        'slug' => Str::slug($fullName) . '-' . ($counter + 1),
                        'part_category_id' => $catId,
                        'thumbnail_path' => $this->pic(600, 400, $counter + 500),
                        'short_description' => "{$desc} Kompatibel untuk {$mName}.",
                        'description' => "<p>{$desc}</p><p>Kompatibel dengan <strong>{$mName}</strong>. Garansi JOMOTO 3 bulan.</p>",
                        'specification' => '',
                        'base_price' => $price,
                        'status' => 'active',
                        'stock_status' => 'ready',
                    ]);

                    // Add specifications for this part
                    $specs = $specsTemplates[$group] ?? [];
                    foreach ($specs as $sIdx => [$specGroup, $key, $value]) {
                        PartSpecification::create([
                            'part_id' => $part->id,
                            'group' => $specGroup,
                            'key' => $key,
                            'value' => $value,
                            'sort_order' => $sIdx,
                        ]);
                    }

                    $this->allPartIds[] = $part->id;
                    $this->motorPartMap[$motorId][] = $part->id;
                    $counter++;
                }
            }
        }
    }

    private function createPartVariants(): void
    {
        $vNames = ['Standard', 'Premium', 'Racing'];
        $mult = [1.0, 1.5, 0.85];

        // Weight ranges (grams) per category group
        $weightByGroup = [
            'Permesinan' => [50, 500],
            'Body' => [150, 2000],
            'Roda dan Suspensi' => [150, 4000],
            'Casis' => [400, 1500],
            'Elektrikal' => [80, 2000],
        ];

        foreach ($this->allPartIds as $i => $partId) {
            $part = Part::with('category')->find($partId);
            if (! $part) continue;
            $bp = (float) $part->base_price;

            // Determine weight range based on category group
            $group = $part->category?->group ?? 'Permesinan';
            [$wMin, $wMax] = $weightByGroup[$group] ?? [100, 1000];

            for ($v = 0; $v < 2; $v++) {
                PartVariant::create([
                    'part_id' => $partId,
                    'sku' => 'VAR-' . str_pad($i * 2 + $v + 1, 5, '0', STR_PAD_LEFT),
                    'name' => $vNames[array_rand($vNames)],
                    'price' => max(15000, round($bp * $mult[$v % 3])),
                    'stock' => rand(5, 200),
                    'weight' => rand($wMin, $wMax),
                    'is_default' => $v === 0,
                ]);
            }
        }
    }

    private function createPartImages(): void
    {
        foreach ($this->allPartIds as $i => $partId) {
            if ($i % 3 !== 0) continue;
            PartImage::create([
                'part_id' => $partId,
                'path' => $this->pic(600, 400, $i + 800),
                'sort_order' => 1,
            ]);
        }
    }

    private function createMotorImages(): void
    {
        foreach ($this->motorIds as $i => $motorId) {
            for ($j = 1; $j <= 3; $j++) {
                MotorImage::create([
                    'motor_id' => $motorId,
                    'path' => $this->pic(800, 600, $i * 10 + $j + 200),
                    'sort_order' => $j,
                ]);
            }
        }
    }

    private function attachMotorParts(): void
    {
        foreach ($this->motorPartMap as $motorId => $partIds) {
            $motor = Motor::find($motorId);
            if ($motor) {
                $motor->parts()->syncWithoutDetaching($partIds);
            }
        }
    }

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
            Banner::create(array_merge($b, ['sort_order' => $i + 1, 'is_active' => true, 'image_path' => $this->pic(1200, 500, $i + 300)]));
        }
        foreach ($promoBanners as $i => $b) {
            Banner::create(array_merge($b, ['sort_order' => $i + 1, 'is_active' => true, 'image_path' => $this->pic(600, 400, $i + 350)]));
        }
        foreach ($launchingBanners as $i => $b) {
            Banner::create(array_merge($b, ['sort_order' => $i + 1, 'is_active' => true, 'image_path' => $this->pic(600, 400, $i + 360)]));
        }
        foreach ($kegiatanBanners as $i => $b) {
            Banner::create(array_merge($b, ['sort_order' => $i + 1, 'is_active' => true, 'image_path' => $this->pic(600, 400, $i + 370)]));
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

    private function createProductHighlights(): void
    {
        $highlightMotors = [0, 1, 2, 4, 5, 8, 10];
        foreach ($highlightMotors as $i => $mIdx) {
            if (isset($this->motorIds[$mIdx])) {
                ProductHighlight::create([
                    'motor_id' => $this->motorIds[$mIdx],
                    'is_active' => true,
                ]);
            }
        }
    }

    private function createNews(): void
    {
        $news = [
            ['JOMOTO Buka Dealer Baru di Surabaya', 'JOMOTO resmi membuka dealer flagship terbaru di Surabaya dengan konsep modern dan lengkap.'],
            ['CFMOTO 450SR Raih Penghargaan Desain', 'CFMOTO 450SR meraih penghargaan desain motor sport terbaik tahun 2025.'],
            ['ZEEHO AE6 Jadi Motor Listrik Terlaris', 'Penjualan ZEEHO AE6 melesat 200% di kuartal pertama 2025.'],
            ['Tips Perawatan Motor Matic', 'Simak tips perawatan motor matic agar tetap prima dan awet.'],
        ];

        foreach ($news as $i => [$title, $content]) {
            News::create([
                'title' => $title,
                'slug' => Str::slug($title),
                'thumbnail_path' => $this->pic(800, 400, $i + 400),
                'content' => "<p>{$content}</p><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>",
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
                'thumbnail_path' => $this->pic(800, 400, $i + 420),
                'description' => "<p>{$desc}</p>",
                'location' => $loc,
                'event_date' => $date,
                'is_active' => true,
            ]);

            for ($g = 1; $g <= 2; $g++) {
                EventGallery::create([
                    'event_id' => $event->id,
                    'path' => $this->pic(800, 600, $i * 10 + $g + 430),
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
                'thumbnail_path' => $this->pic(800, 400, $i + 450),
                'content' => "<p>{$desc}</p><p>Kegiatan CSR ini merupakan bagian dari komitmen JOMOTO untuk berkontribusi positif bagi masyarakat dan lingkungan sekitar.</p>",
                'publish_date' => $date,
                'is_active' => true,
            ]);
        }
    }

    private function createDealers(): void
    {
        $dealers = [
            ['JOMOTO Flagship Jakarta', 'Jl. Sudirman No. 123', 'Jakarta', 'DKI Jakarta', '-6.2088', '106.8456', '(021) 555-1234'],
            ['JOMOTO Bandung', 'Jl. Asia Afrika No. 45', 'Bandung', 'Jawa Barat', '-6.9175', '107.6191', '(022) 555-5678'],
            ['JOMOTO Surabaya', 'Jl. Tunjungan No. 78', 'Surabaya', 'Jawa Timur', '-7.2575', '112.7521', '(031) 555-9012'],
            ['JOMOTO Medan', 'Jl. Gatot Subroto No. 90', 'Medan', 'Sumatra Utara', '3.5952', '98.6722', '(061) 555-3456'],
        ];

        foreach ($dealers as $i => [$name, $addr, $city, $prov, $lat, $lng, $phone]) {
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
            ['Sales Consultant', 'Jakarta', 'Kami mencari sales consultant berpengalaman untuk bergabung di dealer flagship Jakarta.', now()->addDays(14)],
            ['Teknisi Motor', 'Bandung', 'Dibutuhkan teknisi motor berpengalaman minimal 2 tahun.', now()->addDays(21)],
            ['Digital Marketing', 'Jakarta', 'JOMOTO mencari digital marketing specialist untuk mengelola kampanye online.', now()->addDays(7)],
        ];

        foreach ($jobs as [$title, $loc, $desc, $deadline]) {
            Career::create([
                'title' => $title,
                'location' => $loc,
                'description' => "<p>{$desc}</p><h3>Kualifikasi</h3><ul><li>Pengalaman minimal 2 tahun di bidang terkait</li><li>Pendidikan minimal SMA/SMK sederajat</li><li>Memiliki SIM C</li><li>Jujur dan pekerja keras</li></ul>",
                'status' => 'active',
                'publish_date' => now(),
                'is_active' => true,
            ]);
        }
    }

    private function createInternalActivities(): void
    {
        $activities = [
            ['Training Product Knowledge', 'Pelatihan internal untuk sales team mengenai produk terbaru.', now()->subDays(5), 'Training Room'],
            ['Team Building 2025', 'Kegiatan team building tahunan di Puncak.', now()->subDays(30), 'Puncak, Bogor'],
            ['Annual Meeting', 'Rapat tahunan evaluasi kinerja dan perencanaan strategi.', now()->subDays(45), 'Main Office'],
        ];

        foreach ($activities as $i => [$title, $desc, $date, $loc]) {
            $act = InternalActivity::create([
                'title' => $title,
                'slug' => Str::slug($title),
                'thumbnail_path' => $this->pic(800, 400, $i + 470),
                'content' => "<p>{$desc}</p>",
                'publish_date' => $date,
                'is_active' => true,
            ]);

            for ($g = 1; $g <= 2; $g++) {
                \App\Models\InternalActivityGallery::create([
                    'internal_activity_id' => $act->id,
                    'path' => $this->pic(800, 600, $i * 10 + $g + 480),
                    'sort_order' => $g,
                ]);
            }
        }
    }

    private function createPriceLists(): void
    {
        if (empty($this->motorIds)) return;

        $priceLists = [
            ['Daftar Harga Motor Januari 2025', 'list_harga_januari_2025.pdf'],
            ['Daftar Harga Sparepart Q1 2025', 'list_harga_sparepart_q1.pdf'],
        ];

        foreach ($priceLists as $i => [$name, $file]) {
            PriceList::create([
                'motor_id' => $this->motorIds[$i % count($this->motorIds)],
                'name' => $name,
                'pdf_path' => 'storage/price-lists/' . $file,
                'sort_order' => $i + 1,
                'is_active' => true,
            ]);
        }
    }

    private function createPartCatalogs(): void
    {
        if (empty($this->motorIds)) return;

        $catalogs = [
            ['Katalog Sparepart WMOTO', 'storage/catalogs/wmoto_parts.pdf'],
            ['Katalog Sparepart SM Sport', 'storage/catalogs/smsport_parts.pdf'],
            ['Katalog Sparepart CFMOTO', 'storage/catalogs/cfmoto_parts.pdf'],
            ['Katalog Sparepart ZONTES', 'storage/catalogs/zontes_parts.pdf'],
            ['Katalog Sparepart ZEEHO', 'storage/catalogs/zeeho_parts.pdf'],
        ];

        foreach ($catalogs as $i => [$name, $file]) {
            PartCatalog::create([
                'motor_id' => $this->motorIds[$i % count($this->motorIds)],
                'name' => $name,
                'pdf_path' => $file,
                'sort_order' => $i + 1,
                'is_active' => true,
            ]);
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

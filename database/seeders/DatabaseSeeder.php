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
    private array $partCategoryIds = [];
    private array $motorIds = [];
    private array $partIds = [];
    private array $variantIds = [];
    private array $brandIds = [];
    private array $categoryIds = [];

    private function pic(int $w, int $h, int $id): string
    {
        return "https://picsum.photos/{$w}/{$h}?random={$id}";
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
            ['email' => 'admin@motomart.co.id'],
            [
                'role' => 'admin',
                'name' => 'Admin MOTOMART',
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
        $categories = ['Matic', 'Cruiser', 'Naked Bike', 'Sport', 'Touring', 'Adventure', 'Trail', 'EV'];

        foreach ($categories as $i => $name) {
            $cat = MotorCategory::create([
                'name' => $name,
                'slug' => Str::slug($name),
                'sort_order' => $i + 1,
            ]);
            $this->categoryIds[] = $cat->id;
        }
    }

    private function createPartCategories(): void
    {
        $categories = [
            ['oli', 'Oli Mesin'], ['oli', 'Oli Gardan'],
            ['ban', 'Ban Depan'], ['ban', 'Ban Belakang'],
            ['kelistrikan', 'Busi'], ['kelistrikan', 'Aki'], ['kelistrikan', 'Lampu'],
            ['rem', 'Kampas Rem Depan'], ['rem', 'Kampas Rem Belakang'],
            ['body', 'Spion'], ['body', 'Fairing'],
        ];

        foreach ($categories as $i => [$group, $name]) {
            $cat = PartCategory::create([
                'group' => $group,
                'name' => $name,
                'slug' => Str::slug($name).'-'.($i + 1),
                'sort_order' => $i + 1,
            ]);
            $this->partCategoryIds[] = $cat->id;
        }
    }

    private function createMotors(): void
    {
        $motors = [
            ['WMOTO Xtreme 250', 'wmoto', 0, 2025, 45900000, 'Sport naked bike 250cc dengan desain agresif dan performa bertenaga.'],
            ['WMOTO Cruiser 400', 'wmoto', 1, 2024, 72000000, 'Motor cruiser 400cc bergaya klasik dengan kenyamanan maksimal.'],
            ['SM SPORT Urban 150', 'sm-sport', 0, 2025, 28500000, 'Matic urban sporty 150cc dengan teknologi injeksi terbaru.'],
            ['SM SPORT Neo 250', 'sm-sport', 2, 2024, 42000000, 'Naked bike 250cc bergaya neo-retro dengan fitur modern.'],
            ['CFMOTO 450SR', 'cfmoto', 3, 2025, 79900000, 'Sport bike 450cc dengan fairing aerodinamis dan performa superior.'],
            ['CFMOTO 800MT', 'cfmoto', 5, 2024, 165000000, 'Adventure touring 800cc siap jelajah jarak jauh.'],
            ['CFMOTO Papio 125', 'cfmoto', 3, 2025, 35000000, 'Mini sport bike 125cc cocok untuk pemula.'],
            ['ZONTES 350T', 'zontes', 4, 2025, 89000000, 'Touring 350cc dengan fitur lengkap dan kenyamanan premium.'],
            ['ZONTES 350X', 'zontes', 5, 2025, 95000000, 'Adventure 350cc tangguh di segala medan.'],
            ['ZONTES 125U', 'zontes', 2, 2024, 38000000, 'Naked bike 125cc stylish dengan teknologi modern.'],
            ['ZEEHO AE6', 'zeeho', 0, 2025, 28000000, 'Motor listrik matic 1500W untuk mobilitas urban.'],
            ['ZEEHO Magnet', 'zeeho', 7, 2024, 19500000, 'Motor listrik compact ideal untuk perjalanan pendek.'],
        ];

        foreach ($motors as $i => [$name, $brand, $catIdx, $year, $price, $desc]) {
            $motor = Motor::create([
                'brand_id' => $this->brandIds[$brand] ?? null,
                'category_id' => $this->categoryIds[$catIdx] ?? null,
                'name' => $name,
                'slug' => Str::slug($name),
                'year' => $year,
                'price' => $price,
                'thumbnail_path' => $this->pic(600, 400, $i + 50),
                'short_description' => $desc,
                'description' => '<p>' . $desc . '</p><p>Motor ini dilengkapi dengan mesin berperforma tinggi, desain modern, dan fitur keselamatan terkini. Tersedia dalam beberapa pilihan warna menarik.</p>',
                'status' => 'active',
            ]);
            $this->motorIds[] = $motor->id;
        }
    }

    private function createMotorDetails(): void
    {
        $colors = ['Merah', 'Hitam', 'Biru', 'Putih', 'Silver', 'Hijau Army'];
        $colorCodes = ['#dc2626', '#1a1a1a', '#2563eb', '#f5f5f5', '#c0c0c0', '#4d7c0f'];
        $specsData = [
            [['Mesin dan Performa', 'Tipe Mesin', '4 Langkah, SOHC, Pendingin Cairan'], ['Mesin dan Performa', 'Kapasitas', '249cc'], ['Mesin dan Performa', 'Tenaga Maks', '26.5 HP @ 9250 rpm'], ['Mesin dan Performa', 'Torsi Maks', '22.5 Nm @ 7250 rpm'], ['Dimensi dan Berat', 'Panjang', '2,050 mm'], ['Dimensi dan Berat', 'Lebar', '780 mm'], ['Dimensi dan Berat', 'Tinggi', '1,075 mm'], ['Dimensi dan Berat', 'Berat Kosong', '148 kg'], ['Sasis', 'Rangka', 'Diamond Frame'], ['Sasis', 'Suspensi Depan', 'Telescopic 41mm'], ['Sasis', 'Suspensi Belakang', 'Monoshock Adjustable'], ['Fitur', 'ABS', 'Dual Channel'], ['Fitur', 'Panel Instrumen', 'Full Digital LCD'], ['Fitur', 'Lampu', 'Full LED']],
            [['Mesin dan Performa', 'Tipe Mesin', '4 Langkah, V-Twin, Pendingin Udara'], ['Mesin dan Performa', 'Kapasitas', '396cc'], ['Mesin dan Performa', 'Tenaga Maks', '35 HP @ 8000 rpm'], ['Dimensi dan Berat', 'Panjang', '2,240 mm'], ['Dimensi dan Berat', 'Lebar', '920 mm'], ['Dimensi dan Berat', 'Berat Kosong', '195 kg'], ['Sasis', 'Rangka', 'Double Cradle Steel'], ['Fitur', 'Sistem Injeksi', 'EFI Bosch']],
            [['Mesin dan Performa', 'Tipe Mesin', '4 Langkah, SOHC, Pendingin Cairan'], ['Mesin dan Performa', 'Kapasitas', '149cc'], ['Mesin dan Performa', 'Tenaga Maks', '12 HP @ 8500 rpm'], ['Dimensi dan Berat', 'Berat Kosong', '112 kg'], ['Sasis', 'Rangka', 'Underbone Steel'], ['Fitur', 'Sistem Kunci', 'Smart Keyless']],
        ];

        foreach ($this->motorIds as $i => $motorId) {
            for ($c = 0; $c < 3; $c++) {
                MotorColor::create([
                    'motor_id' => $motorId,
                    'name' => $colors[($i + $c) % count($colors)],
                    'color_code' => $colorCodes[($i + $c) % count($colorCodes)],
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
        $parts = [
            ['Oli Mesin MForce 1L', 0, 65000, 'Oli mesin original untuk semua motor MForce.'],
            ['Oli Gardan Racing 120ml', 1, 35000, 'Oli gardan performa tinggi.'],
            ['Ban Depan IRC 90/80-17', 2, 450000, 'Ban depan tubeless IRC premium.'],
            ['Ban Belakang Pirelli 140/70-17', 3, 750000, 'Ban belakang Pirelli Diablo Rosso.'],
            ['Busi NGK Racing CR8E', 4, 55000, 'Busi performa tinggi untuk motor sport.'],
            ['Aki Maintenance Free YTZ7S', 5, 250000, 'Aki kering bebas perawatan.'],
            ['Lampu LED Headlamp Universal', 6, 185000, 'Lampu utama LED super terang.'],
            ['Kampas Rem Depan Brembo', 7, 125000, 'Kampas rem depan premium Brembo.'],
            ['Kampas Rem Belakang EBC', 8, 95000, 'Kampas rem belakang EBC berkualitas.'],
            ['Spion Foldable Universal', 9, 85000, 'Spion lipat universal untuk semua tipe.'],
            ['Fairing Samping WMOTO', 10, 350000, 'Fairing samping original WMOTO.'],
        ];

        foreach ($parts as $i => [$name, $catIdx, $price, $desc]) {
            $part = Part::create([
                'sku' => 'MP-'.str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'name' => $name,
                'slug' => Str::slug($name).'-'.($i + 1),
                'part_category_id' => $this->partCategoryIds[$catIdx],
                'thumbnail_path' => $this->pic(600, 400, $i + 100),
                'short_description' => $desc,
                'base_price' => $price,
                'status' => 'active',
            ]);
            $this->partIds[] = $part->id;
        }
    }

    private function createPartVariants(): void
    {
        $variants = [
            [0, 'Botol 1 Liter', 65000, 100, true],
            [1, 'Tube 120ml', 35000, 200, true],
            [2, '90/80-17', 450000, 30, true],
            [3, '140/70-17', 750000, 20, true],
            [4, 'CR8E Standard', 55000, 150, true],
            [5, 'YTZ7S 12V', 250000, 50, true],
        ];

        foreach ($variants as $i => [$partIdx, $name, $price, $stock, $default]) {
            $variant = PartVariant::create([
                'part_id' => $this->partIds[$partIdx],
                'sku' => 'VAR-'.str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'name' => $name,
                'price' => $price,
                'stock' => $stock,
                'is_default' => $default,
            ]);
            $this->variantIds[] = $variant->id;
        }
    }

    private function createPartImages(): void
    {
        foreach ($this->partIds as $i => $partId) {
            PartImage::create([
                'part_id' => $partId,
                'path' => $this->pic(600, 400, $i + 120),
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
        $mappings = [[0, 0], [1, 1], [2, 2], [3, 3], [4, 4], [0, 5], [1, 6], [2, 7], [3, 8], [4, 9], [0, 10]];
        foreach ($mappings as [$mIdx, $pIdx]) {
            if (isset($this->motorIds[$mIdx], $this->partIds[$pIdx])) {
                Motor::find($this->motorIds[$mIdx])->parts()->attach($this->partIds[$pIdx]);
            }
        }
    }

    private function createBanners(): void
    {
        $heroBanners = [
            ['title' => 'MOTOMART 2025', 'subtitle' => 'New Collection', 'button' => 'Jelajahi Produk', 'link' => '/produk', 'type' => 'hero'],
            ['title' => 'CFMOTO 450SR', 'subtitle' => 'Sport Performance', 'button' => 'Lihat Detail', 'link' => '#', 'type' => 'hero'],
            ['title' => 'ZEEHO Electric', 'subtitle' => 'EV Future', 'button' => 'Selengkapnya', 'link' => '/produk?brand=zeeho', 'type' => 'hero'],
        ];

        $promoBanners = [
            ['title' => 'Promo Akhir Tahun', 'subtitle' => 'Diskon Hingga 5 Juta', 'button' => 'Lihat Promo', 'link' => '#', 'type' => 'promo'],
            ['title' => 'Gratis Service 4x', 'subtitle' => 'Untuk Pembelian Motor Baru', 'button' => 'Syarat & Ketentuan', 'link' => '#', 'type' => 'promo'],
            ['title' => 'Trade-In Motor Lama', 'subtitle' => 'Tukar Tambah Harga Tinggi', 'button' => 'Cek Sekarang', 'link' => '#', 'type' => 'promo'],
        ];

        $launchingBanners = [
            ['title' => 'ZONTES 350X Adventure', 'subtitle' => 'Launching Produk Baru', 'button' => 'Lihat Detail', 'link' => '#', 'type' => 'launching'],
            ['title' => 'ZEEHO Magnet EV', 'subtitle' => 'Coming Soon', 'button' => 'Pre-Order', 'link' => '#', 'type' => 'launching'],
        ];

        $kegiatanBanners = [
            ['title' => 'MOTOMART Fest 2025', 'subtitle' => 'Gathering Komunitas Motor', 'button' => 'Lihat Event', 'link' => '#', 'type' => 'kegiatan'],
            ['title' => 'CSR Goes to School', 'subtitle' => 'Edukasi Safety Riding', 'button' => 'Selengkapnya', 'link' => '#', 'type' => 'kegiatan'],
        ];

        $allBanners = array_merge($heroBanners, $promoBanners, $launchingBanners, $kegiatanBanners);
        foreach ($allBanners as $i => $b) {
            Banner::create([
                'title' => $b['title'],
                'type' => $b['type'],
                'subtitle' => $b['subtitle'],
                'image_path' => $this->pic(1400, 700, $i + 300),
                'link_url' => $b['link'],
                'button_text' => $b['button'] ?? null,
                'is_active' => true,
                'sort_order' => $i + 1,
            ]);
        }
    }

    private function createWhyChooseUs(): void
    {
        $items = [
            ['Kualitas Produk', 'Semua motor kami melewati quality control ketat sesuai standar internasional.', '&#9733;'],
            ['Jaringan Dealer', 'Lebih dari 100 dealer resmi tersebar di seluruh Indonesia.', '&#x1F3EA;'],
            ['Sparepart Tersedia', 'Suku cadang asli selalu tersedia dengan jaminan keaslian.', '&#x1F527;'],
            ['Garansi Resmi', 'Garansi resmi 3 tahun atau 30.000 km untuk setiap pembelian.', '&#x1F4DC;'],
            ['Teknologi Modern', 'Motor dilengkapi teknologi terkini: ABS, LED, Smart Key.', '&#x26A1;'],
            ['Layanan Purna Jual', 'Bengkel resmi dengan mekanik bersertifikasi siap membantu.', '&#x1F468;&#x200D;&#x1F527;'],
        ];

        foreach ($items as $i => [$title, $desc, $icon]) {
            WhyChooseUs::create([
                'title' => $title,
                'description' => $desc,
                'icon' => $icon,
                'sort_order' => $i + 1,
                'is_active' => true,
            ]);
        }
    }

    private function createProductHighlights(): void
    {
        if (!empty($this->motorIds)) {
            ProductHighlight::create([
                'motor_id' => $this->motorIds[4],
                'is_active' => true,
            ]);
        }
    }

    private function createNews(): void
    {
        $articles = [
            ['MOTOMART Luncurkan Brand ZEEHO Motor Listrik di Indonesia', 'Peluncuran resmi brand motor listrik ZEEHO sebagai langkah MOTOMART mendukung transisi kendaraan ramah lingkungan.', 'MOTOMART', 'Peluncuran'],
            ['CFMOTO 450SR Raih Penghargaan Motor Sport Terbaik 2025', 'CFMOTO 450SR berhasil meraih penghargaan bergengsi sebagai motor sport terbaik dalam ajang Otomotif Award 2025.', 'MOTOMART', 'Penghargaan'],
            ['MOTOMART Buka 20 Dealer Baru di Jawa Timur', 'Ekspansi jaringan dealer terus dilakukan untuk memberikan layanan terbaik kepada pelanggan di seluruh Indonesia.', 'MOTOMART', 'Bisnis'],
            ['Tips Merawat Motor Sport Agar Performa Tetap Prima', 'Panduan lengkap perawatan motor sport dari mekanik profesional MOTOMART.', 'MOTOMART', 'Tips'],
            ['Program Tukar Tambah MOTOMART Disambut Antusias', 'Program tukar tambah motor lama dengan nilai tinggi mendapat respon positif dari konsumen.', 'MOTOMART', 'Promo'],
            ['MOTOMART Support Event Balap Nasional ISSOM 2025', 'MOTOMART menjadi sponsor utama kejuaraan balap motor nasional ISSOM 2025.', 'MOTOMART', 'Event'],
        ];

        foreach ($articles as $i => [$title, $content, $author, $cat]) {
            News::create([
                'title' => $title,
                'slug' => Str::slug($title),
                'thumbnail_path' => $this->pic(800, 500, $i + 400),
                'content' => '<p>' . $content . '</p><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>',
                'author' => $author,
                'category' => $cat,
                'publish_date' => now()->subDays(($i + 1) * 3),
                'is_active' => true,
            ]);
        }
    }

    private function createEvents(): void
    {
        $events = [
            ['MOTOMART Fest 2025', 'Festival akbar komunitas motor MOTOMART dengan berbagai kegiatan seru.', 'JCC Senayan, Jakarta', now()->addDays(14)],
            ['Riding Together Chapter Bandung', 'Touring bersama komunitas motor MOTOMART Chapter Bandung.', 'Bandung - Lembang', now()->subDays(20)],
            ['Test Ride Weekend CFMOTO', 'Kesempatan test ride motor CFMOTO terbaru selama weekend.', 'Dealer MOTOMART Surabaya', now()->subDays(45)],
            ['Safety Riding Workshop', 'Workshop edukasi safety riding untuk pelajar dan komunitas.', 'Gedung Balai Kota', now()->addDays(30)],
        ];

        foreach ($events as $i => [$title, $desc, $loc, $date]) {
            $event = Event::create([
                'title' => $title,
                'slug' => Str::slug($title),
                'thumbnail_path' => $this->pic(800, 500, $i + 500),
                'description' => $desc,
                'content' => '<p>' . $desc . '</p><p>Acara ini akan diisi dengan berbagai kegiatan menarik termasuk meet & greet, riding bersama, doorprize, dan masih banyak lagi. Jangan lewatkan kesempatan untuk bergabung!</p>',
                'location' => $loc,
                'event_date' => $date,
                'is_active' => true,
            ]);

            for ($j = 1; $j <= 3; $j++) {
                EventGallery::create([
                    'event_id' => $event->id,
                    'path' => $this->pic(800, 600, $i * 10 + $j + 500),
                    'type' => 'image',
                    'sort_order' => $j,
                ]);
            }
        }
    }

    private function createCsr(): void
    {
        $articles = [
            ['MOTOMART Peduli: Bantuan untuk Korban Bencana Alam', 'MOTOMART menyalurkan bantuan kemanusiaan untuk korban bencana alam di berbagai wilayah Indonesia.'],
            ['Program Beasiswa MOTOMART untuk Pelajar Berprestasi', 'MOTOMART meluncurkan program beasiswa pendidikan bagi pelajar berprestasi dari keluarga kurang mampu.'],
            ['Green Initiative: Penanaman 10.000 Pohon', 'MOTOMART bekerja sama dengan komunitas lingkungan untuk program penghijauan nasional.'],
            ['Edukasi Safety Riding Goes to School', 'Program edukasi keselamatan berkendara untuk pelajar SMA di 50 kota.'],
        ];

        foreach ($articles as $i => [$title, $content]) {
            CsrArticle::create([
                'title' => $title,
                'slug' => Str::slug($title),
                'thumbnail_path' => $this->pic(800, 500, $i + 600),
                'content' => '<p>' . $content . '</p><p>Kami percaya bahwa tanggung jawab sosial adalah bagian integral dari bisnis yang berkelanjutan. MOTOMART berkomitmen untuk terus memberikan dampak positif bagi masyarakat dan lingkungan.</p>',
                'documentation' => json_encode([$this->pic(800, 600, $i + 650), $this->pic(800, 600, $i + 660)]),
                'publish_date' => now()->subDays($i * 10 + 5),
                'is_active' => true,
            ]);
        }
    }

    private function createDealers(): void
    {
        $dealers = [
            ['MOTOMART Jakarta Pusat', 'Jl. Raya Motor No. 123, Tanah Abang', 'Jakarta Pusat', 'DKI Jakarta', '021-1234567', '081234567890', 'jakarta@motomart.co.id'],
            ['MOTOMART Surabaya', 'Jl. Ahmad Yani No. 45', 'Surabaya', 'Jawa Timur', '031-7654321', '081987654321', 'surabaya@motomart.co.id'],
            ['MOTOMART Bandung', 'Jl. Soekarno Hatta No. 78', 'Bandung', 'Jawa Barat', '022-1122334', '085612345678', 'bandung@motomart.co.id'],
            ['MOTOMART Medan', 'Jl. Gatot Subroto No. 10', 'Medan', 'Sumatera Utara', '061-4455667', '082112345678', 'medan@motomart.co.id'],
            ['MOTOMART Yogyakarta', 'Jl. Magelang Km 5 No. 22', 'Yogyakarta', 'DI Yogyakarta', '0274-889900', '081345678912', 'jogja@motomart.co.id'],
            ['MOTOMART Semarang', 'Jl. Pemuda No. 33', 'Semarang', 'Jawa Tengah', '024-5566778', '087712345678', 'semarang@motomart.co.id'],
            ['MOTOMART Makassar', 'Jl. AP Pettarani No. 50', 'Makassar', 'Sulawesi Selatan', '0411-990011', '085212345678', 'makassar@motomart.co.id'],
            ['MOTOMART Denpasar', 'Jl. Gatot Subroto No. 15', 'Denpasar', 'Bali', '0361-223344', '083345678912', 'bali@motomart.co.id'],
        ];

        foreach ($dealers as $i => [$name, $addr, $city, $prov, $phone, $wa, $email]) {
            Dealer::create([
                'name' => $name,
                'address' => $addr,
                'city' => $city,
                'province' => $prov,
                'phone' => $phone,
                'whatsapp' => $wa,
                'email' => $email,
                'google_maps_url' => 'https://maps.google.com/?q=' . urlencode("{$name} {$addr}"),
                'is_active' => true,
                'sort_order' => $i + 1,
            ]);
        }
    }

    private function createCareers(): void
    {
        $careers = [
            ['Sales Consultant', 'Jakarta', '<p>Bertanggung jawab melayani pelanggan dan mencapai target penjualan.</p>', '<ul><li>Pendidikan minimal SMA/SMK</li><li>Berpengalaman di bidang sales minimal 1 tahun</li><li>Memiliki SIM C</li><li>Komunikatif dan berorientasi target</li></ul>'],
            ['Teknisi Bengkel', 'Surabaya', '<p>Melakukan perawatan dan perbaikan motor pelanggan.</p>', '<ul><li>Lulusan SMK Teknik Otomotif</li><li>Pengalaman minimal 2 tahun</li><li>Menguasai mesin motor injeksi</li><li>Bersertifikat mekanik</li></ul>'],
            ['Admin Marketing', 'Bandung', '<p>Mendukung tim marketing dalam kegiatan promosi dan event.</p>', '<ul><li>Pendidikan D3/S1 Marketing atau Komunikasi</li><li>Mahir media sosial</li><li>Kreatif dan proaktif</li></ul>'],
            ['Customer Service', 'Jakarta', '<p>Menangani keluhan dan pertanyaan pelanggan.</p>', '<ul><li>Pendidikan minimal SMA/SMK</li><li>Pengalaman CS minimal 1 tahun</li><li>Sabar dan komunikatif</li><li>Menguasai Ms. Office</li></ul>'],
        ];

        foreach ($careers as $i => [$title, $loc, $desc, $req]) {
            Career::create([
                'title' => $title,
                'location' => $loc,
                'description' => $desc,
                'requirements' => $req,
                'publish_date' => now()->subDays($i * 4),
                'status' => 'active',
                'is_active' => true,
            ]);
        }
    }

    private function createInternalActivities(): void
    {
        $activities = [
            ['Team Building MOTOMART Jogja 2025', 'Kegiatan team building tahunan di Yogyakarta untuk mempererat kerjasama tim.'],
            ['Sharing Session Technology Update', 'Sesi berbagi pengetahuan tentang teknologi motor terkini.'],
            ['Hari Olahraga Nasional MOTOMART', 'Turnamen olahraga internal antar cabang MOTOMART.'],
            ['Workshop Service Excellence', 'Pelatihan layanan pelanggan untuk seluruh staff frontliner.'],
        ];

        foreach ($activities as $i => [$title, $content]) {
            $act = InternalActivity::create([
                'title' => $title,
                'slug' => Str::slug($title),
                'thumbnail_path' => $this->pic(800, 500, $i + 700),
                'content' => '<p>' . $content . '</p><p>Kegiatan internal ini merupakan bagian dari program pengembangan SDM MOTOMART yang berkelanjutan, bertujuan untuk meningkatkan kompetensi dan kebersamaan seluruh karyawan.</p>',
                'publish_date' => now()->subDays($i * 8 + 3),
                'is_active' => true,
            ]);

            for ($j = 1; $j <= 2; $j++) {
                $act->galleries()->create([
                    'path' => $this->pic(800, 600, $i * 10 + $j + 800),
                    'sort_order' => $j,
                ]);
            }
        }
    }

    private function createPriceLists(): void
    {
        foreach ($this->motorIds as $i => $motorId) {
            if ($i >= 6) break;
            $motor = Motor::find($motorId);
            PriceList::create([
                'motor_id' => $motorId,
                'name' => 'Daftar Harga Spare Part ' . ($motor->name ?? 'Motor'),
                'pdf_path' => 'storage/price-lists/sample.pdf',
                'is_active' => true,
                'sort_order' => $i + 1,
            ]);
        }
    }

    private function createPartCatalogs(): void
    {
        foreach ($this->motorIds as $i => $motorId) {
            if ($i >= 6) break;
            $motor = Motor::find($motorId);
            PartCatalog::create([
                'motor_id' => $motorId,
                'name' => 'Katalog Part ' . ($motor->name ?? 'Motor'),
                'pdf_path' => 'storage/part-catalogs/sample.pdf',
                'is_active' => true,
                'sort_order' => $i + 1,
            ]);
        }
    }

    private function createCompanyProfile(): void
    {
        CompanyProfile::updateOrCreate(
            ['key' => 'sejarah'],
            ['value' => '<p>MOTOMART didirikan pada tahun 2010 sebagai dealer resmi motor premium di Indonesia. Berawal dari satu showroom di Jakarta, kini MOTOMART telah berkembang menjadi jaringan dealer nasional dengan lebih dari 100 cabang yang tersebar di seluruh Indonesia. MOTOMART konsisten menghadirkan motor berkualitas dari brand-brand ternama seperti WMOTO, SM SPORT, CFMOTO, ZONTES, dan ZEEHO.</p>']
        );

        CompanyProfile::updateOrCreate(
            ['key' => 'visi'],
            ['value' => 'Menjadi jaringan dealer motor premium terdepan di Indonesia yang memberikan pengalaman berkendara terbaik bagi pelanggan.']
        );

        CompanyProfile::updateOrCreate(
            ['key' => 'misi'],
            ['value' => '<ul><li>Menyediakan produk motor berkualitas dengan harga kompetitif.</li><li>Memberikan layanan purna jual terbaik melalui bengkel resmi.</li><li>Memperluas jaringan dealer untuk menjangkau lebih banyak pelanggan.</li><li>Mendukung gaya hidup berkendara yang aman dan bertanggung jawab.</li></ul>']
        );

        CompanyProfile::updateOrCreate(
            ['key' => 'nilai'],
            ['value' => "Integritas\nInovasi\nKepuasan Pelanggan\nKerjasama Tim\nTanggung Jawab Sosial"]
        );
    }
}

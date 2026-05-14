<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Banner;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Motor;
use App\Models\MotorImage;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Part;
use App\Models\PartCategory;
use App\Models\PartImage;
use App\Models\PartVariant;
use App\Models\Payment;
use App\Models\Shipment;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    private array $partCategoryIds = [];
    private array $motorIds = [];
    private array $partIds = [];
    private array $variantIds = [];
    private array $userId = [];

    public function run(): void
    {
        $this->createUsers();
        $this->createBanners();
        $this->createPartCategories();
        $this->createMotors();
        $this->createParts();
        $this->createPartVariants();
        $this->createPartImages();
        $this->createMotorImages();
        $this->attachMotorParts();
        $this->createAddresses();
        $this->createCarts();
        $this->createCartItems();
        $this->createWishlists();
        $this->createOrders();
        $this->createOrderItems();
        $this->createPayments();
        $this->createShipments();
    }

    private function createUsers(): void
    {
        $adminEmail = env('ADMIN_EMAIL');
        $adminPassword = env('ADMIN_PASSWORD');

        if ($adminEmail && $adminPassword) {
            User::updateOrCreate(
                ['email' => $adminEmail],
                [
                    'role' => 'admin',
                    'name' => env('ADMIN_NAME', 'Admin'),
                    'password' => Hash::make($adminPassword),
                ]
            );
        }

        $buyers = [
            ['Budi Santoso', 'budi@example.com'],
            ['Siti Rahmawati', 'siti@example.com'],
            ['Ahmad Hidayat', 'ahmad@example.com'],
            ['Dewi Lestari', 'dewi@example.com'],
            ['Rudi Hermawan', 'rudi@example.com'],
            ['Maya Anggraini', 'maya@example.com'],
            ['Agus Wijaya', 'agus@example.com'],
            ['Rina Marlina', 'rina@example.com'],
            ['Doni Prasetyo', 'doni@example.com'],
            ['Fitri Handayani', 'fitri@example.com'],
        ];

        foreach ($buyers as [$name, $email]) {
            $user = User::factory()->create([
                'role' => 'buyer',
                'name' => $name,
                'email' => $email,
                'password' => Hash::make('password'),
            ]);
            $this->userId[] = $user->id;
        }
    }

    private function createBanners(): void
    {
        $banners = [
            ['New Collection 2026', '/motors', true, 1],
            ['Original Spare Parts', '/parts', true, 2],
            ['Season Sale Special', '#', true, 3],
            ['Motorcycle Lifestyle', '#', true, 4],
        ];

        foreach ($banners as [$title, $link, $active, $sort]) {
            Banner::create([
                'title' => $title,
                'image_path' => "storage/banner/wallpaper{$sort}.jpg",
                'link_url' => $link,
                'is_active' => $active,
                'sort_order' => $sort,
            ]);
        }
    }

    private function createPartCategories(): void
    {
        $categories = [
            ['oli', 'Oli Mesin'],
            ['oli', 'Oli Gardan'],
            ['ban', 'Ban Depan'],
            ['ban', 'Ban Belakang'],
            ['kelistrikan', 'Busi'],
            ['kelistrikan', 'Aki'],
            ['kelistrikan', 'Lampu'],
            ['rem', 'Kampas Rem Depan'],
            ['rem', 'Kampas Rem Belakang'],
            ['body', 'Spion'],
        ];

        foreach ($categories as $i => [$group, $name]) {
            $cat = PartCategory::create([
                'group' => $group,
                'name' => $name,
                'slug' => Str::slug($name) . '-' . ($i + 1),
                'sort_order' => $i + 1,
            ]);
            $this->partCategoryIds[] = $cat->id;
        }
    }

    private function createMotors(): void
    {
        $motors = [
            ['Honda Vario 160', 2024, 'Honda matic 160cc premium'],
            ['Yamaha NMAX Connected', 2024, 'Yamaha matic 155cc connectivity'],
            ['Honda PCX 160', 2023, 'Honda matic premium 160cc'],
            ['Yamaha Aerox 155', 2024, 'Yamaha matic sporty 155cc'],
        ];

        foreach ($motors as $i => [$name, $year, $desc]) {
            $motor = Motor::create([
                'name' => $name,
                'slug' => Str::slug($name),
                'year' => $year,
                'thumbnail_path' => "storage/products/produk" . ($i + 1) . ".jpeg",
                'short_description' => $desc,
                'status' => 'published',
            ]);
            $this->motorIds[] = $motor->id;
        }
    }

    private function createParts(): void
    {
        $parts = [
            ['Oli Mesin AHM 0.8L', $this->partCategoryIds[0], 55000, 'Oli mesin original Honda untuk motor matic'],
            ['Ban Depan Michelin City Grip', $this->partCategoryIds[2], 350000, 'Ban depan ukuran 80/80-14'],
            ['Busi NGK Iridium CR7HGP', $this->partCategoryIds[4], 85000, 'Busi iridium performa optimal'],
            ['Spion Retro Bulat Universal', $this->partCategoryIds[9], 45000, 'Spion bulat retro custom'],
        ];

        foreach ($parts as $i => [$name, $catId, $price, $desc]) {
            $part = Part::create([
                'sku' => 'SKU-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'name' => $name,
                'slug' => Str::slug($name) . '-' . ($i + 1),
                'part_category_id' => $catId,
                'thumbnail_path' => "storage/products/produk" . ($i + 1) . ".jpeg",
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
            [0, 'Kemasan 0.8L', 55000, 50, true],
            [1, 'Ukuran 80/80-14', 350000, 15, true],
            [2, 'CR7HGP', 85000, 100, true],
            [3, 'Hitam Retro', 45000, 75, true],
        ];

        foreach ($variants as $i => [$partIdx, $name, $price, $stock, $default]) {
            $variant = PartVariant::create([
                'part_id' => $this->partIds[$partIdx],
                'sku' => 'VAR-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
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
        for ($i = 0; $i < 4; $i++) {
            PartImage::create([
                'part_id' => $this->partIds[$i],
                'path' => "storage/products/produk" . ($i + 1) . ".jpeg",
                'sort_order' => 1,
            ]);
        }
    }

    private function createMotorImages(): void
    {
        for ($i = 0; $i < 4; $i++) {
            MotorImage::create([
                'motor_id' => $this->motorIds[$i],
                'path' => "storage/products/produk" . ($i + 1) . ".jpeg",
                'sort_order' => 1,
            ]);
        }
    }

    private function attachMotorParts(): void
    {
        $motorParts = [
            [0, 0], [1, 1], [2, 2], [3, 3],
        ];

        foreach ($motorParts as [$motorIdx, $partIdx]) {
            Motor::find($this->motorIds[$motorIdx])->parts()->attach($this->partIds[$partIdx]);
        }
    }

    private function createAddresses(): void
    {
        $addresses = [
            [0, 'Rumah', 'Jl. Merdeka No. 10', 'Jakarta Pusat', 'DKI Jakarta', '10110'],
            [1, 'Kantor', 'Jl. Sudirman Kav. 25', 'Jakarta Selatan', 'DKI Jakarta', '12190'],
            [2, 'Rumah', 'Jl. Diponegoro No. 88', 'Bandung', 'Jawa Barat', '40115'],
            [3, 'Rumah', 'Jl. Pemuda No. 45', 'Semarang', 'Jawa Tengah', '50132'],
            [4, 'Rumah', 'Jl. Kaliurang KM 5', 'Sleman', 'DI Yogyakarta', '55281'],
            [5, 'Rumah', 'Jl. Darmo Permai No. 12', 'Surabaya', 'Jawa Timur', '60115'],
            [6, 'Rumah', 'Jl. Gajah Mada No. 7', 'Denpasar', 'Bali', '80231'],
            [7, 'Rumah', 'Jl. Veteran No. 33', 'Medan', 'Sumatera Utara', '20131'],
            [8, 'Kantor', 'Jl. Asia Afrika No. 1', 'Makassar', 'Sulawesi Selatan', '90111'],
            [9, 'Rumah', 'Jl. Rajawali No. 5', 'Palembang', 'Sumatera Selatan', '30114'],
        ];

        foreach ($addresses as $i => [$userIdx, $label, $line1, $city, $province, $postal]) {
            Address::create([
                'user_id' => $this->userId[$userIdx],
                'label' => $label,
                'recipient_name' => User::find($this->userId[$userIdx])->name,
                'phone' => '0812' . str_pad(mt_rand(1000000, 9999999), 7, '0', STR_PAD_LEFT),
                'address_line1' => $line1,
                'city' => $city,
                'province' => $province,
                'postal_code' => $postal,
                'is_default' => true,
            ]);
        }
    }

    private function createCarts(): void
    {
        foreach ($this->userId as $uid) {
            Cart::create(['user_id' => $uid]);
        }
    }

    private function createCartItems(): void
    {
        $items = [
            [0, 0, 2], [1, 1, 1], [2, 2, 3], [3, 3, 1],
            [4, 0, 1], [5, 1, 2], [6, 2, 1], [7, 3, 2],
        ];

        foreach ($items as [$userIdx, $variantIdx, $qty]) {
            $cart = Cart::where('user_id', $this->userId[$userIdx])->first();
            $variant = PartVariant::find($this->variantIds[$variantIdx]);

            CartItem::create([
                'cart_id' => $cart->id,
                'part_variant_id' => $variant->id,
                'quantity' => $qty,
                'price_snapshot' => $variant->price,
            ]);
        }
    }

    private function createWishlists(): void
    {
        $wishlists = [
            [0, 0], [1, 1], [2, 2], [3, 3],
            [4, 0], [5, 1], [6, 2], [7, 3],
        ];

        foreach ($wishlists as [$userIdx, $partIdx]) {
            Wishlist::create([
                'user_id' => $this->userId[$userIdx],
                'part_id' => $this->partIds[$partIdx],
            ]);
        }
    }

    private function createOrders(): void
    {
        $statuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered'];

        foreach ($this->userId as $i => $uid) {
            $status = $statuses[$i % count($statuses)];

            Order::create([
                'user_id' => $uid,
                'order_no' => 'INV/' . now()->format('Ymd') . '/' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'status' => $status,
                'subtotal' => 100000 + ($i * 50000),
                'shipping_cost' => 25000,
                'total' => 125000 + ($i * 50000),
                'address_snapshot' => [
                    'recipient_name' => User::find($uid)->name,
                    'phone' => '0812' . str_pad(mt_rand(1000000, 9999999), 7, '0', STR_PAD_LEFT),
                    'address' => 'Jl. Contoh No. ' . ($i + 1),
                    'city' => 'Jakarta',
                    'province' => 'DKI Jakarta',
                    'postal_code' => '10110',
                ],
            ]);
        }
    }

    private function createOrderItems(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $orderId = $i + 1;
            $partIdx = $i % 4;
            $part = Part::find($this->partIds[$partIdx]);
            $variant = PartVariant::where('part_id', $part->id)->first();

            OrderItem::create([
                'order_id' => $orderId,
                'part_id' => $part->id,
                'part_variant_id' => $variant?->id,
                'sku' => $part->sku,
                'name' => $part->name,
                'variant_name' => $variant?->name,
                'price' => $variant?->price ?? $part->base_price,
                'quantity' => 1,
                'line_total' => $variant?->price ?? $part->base_price,
            ]);
        }
    }

    private function createPayments(): void
    {
        $statuses = [
            'pending', 'settlement', 'pending', 'settlement', 'settlement',
            'settlement', 'pending', 'settlement', 'settlement', 'settlement',
        ];

        foreach ($statuses as $i => $status) {
            Payment::create([
                'order_id' => $i + 1,
                'provider' => 'midtrans',
                'provider_reference' => 'TRX-' . str_pad($i + 1, 6, '0', STR_PAD_LEFT),
                'status' => $status,
                'payload' => ['payment_method' => 'bank_transfer', 'bank' => 'BCA'],
            ]);
        }
    }

    private function createShipments(): void
    {
        $statuses = [
            'pending', 'pending', 'pending', 'shipped', 'delivered',
            'delivered', 'pending', 'shipped', 'delivered', 'delivered',
        ];

        $couriers = ['JNE', 'J&T', 'SiCepat', 'JNE', 'J&T', 'SiCepat', 'JNE', 'J&T', 'SiCepat', 'JNE'];

        foreach ($statuses as $i => $status) {
            Shipment::create([
                'order_id' => $i + 1,
                'provider' => 'biteship',
                'courier' => $couriers[$i],
                'service' => 'REG',
                'tracking_number' => $status !== 'pending' ? 'TRK' . str_pad($i + 1, 10, '0', STR_PAD_LEFT) : null,
                'status' => $status,
                'payload' => ['estimated_delivery' => now()->addDays(3)->toDateString()],
            ]);
        }
    }
}
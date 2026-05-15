<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\Motor;
use App\Models\MotorImage;
use App\Models\Part;
use App\Models\PartCategory;
use App\Models\PartImage;
use App\Models\PartVariant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    private array $partCategoryIds = [];
    private array $motorIds = [];
    private array $partIds = [];
    private array $variantIds = [];

    public function run(): void
    {
        $this->createAdmin();
        $this->createBanners();
        $this->createPartCategories();
        $this->createMotors();
        $this->createParts();
        $this->createPartVariants();
        $this->createPartImages();
        $this->createMotorImages();
        $this->attachMotorParts();
    }

    private function createAdmin(): void
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
                'slug' => Str::slug($name).'-'.($i + 1),
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
                'thumbnail_path' => "storage/products/produk".($i + 1).".jpeg",
                'short_description' => $desc,
                'status' => 'active',
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
                'sku' => 'SKU-'.str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'name' => $name,
                'slug' => Str::slug($name).'-'.($i + 1),
                'part_category_id' => $catId,
                'thumbnail_path' => "storage/products/produk".($i + 1).".jpeg",
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
        for ($i = 0; $i < 4; $i++) {
            PartImage::create([
                'part_id' => $this->partIds[$i],
                'path' => "storage/products/produk".($i + 1).".jpeg",
                'sort_order' => 1,
            ]);
        }
    }

    private function createMotorImages(): void
    {
        for ($i = 0; $i < 4; $i++) {
            MotorImage::create([
                'motor_id' => $this->motorIds[$i],
                'path' => "storage/products/produk".($i + 1).".jpeg",
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
}

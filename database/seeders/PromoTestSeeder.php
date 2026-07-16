<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;

class PromoTestSeeder extends Seeder
{
    public function run(): void
    {
        $promos = [
            [
                'title' => 'Cashback 50% Motor Listrik',
                'subtitle' => 'Dapatkan cashback hingga Rp 5.000.000 untuk pembelian motor listrik ZEEHO',
                'image_path' => 'https://picsum.photos/seed/promo1/800/600',
                'link_url' => 'https://example.com/promo1',
                'button_text' => 'Klaim Sekarang',
                'sort_order' => 1,
                'type' => 'promo',
                'is_active' => true,
            ],
            [
                'title' => 'Bonus Helm & Sarung Tangan',
                'subtitle' => 'Setiap pembelian motor CFMOTO dapatkan helm dan sarung tangan original',
                'image_path' => 'https://picsum.photos/seed/promo2/800/600',
                'link_url' => 'https://example.com/promo2',
                'button_text' => 'Lihat Detail',
                'sort_order' => 2,
                'type' => 'promo',
                'is_active' => true,
            ],
            [
                'title' => 'DP Ringan Mulai 20%',
                'subtitle' => 'Cicilan ringan dengan DP hanya 20% untuk semua motor WMOTO',
                'image_path' => 'https://picsum.photos/seed/promo3/800/600',
                'link_url' => 'https://example.com/promo3',
                'button_text' => 'Ajukan Kredit',
                'sort_order' => 3,
                'type' => 'promo',
                'is_active' => true,
            ],
            [
                'title' => 'Service Gratis 1 Tahun',
                'subtitle' => 'Gratis biaya jasa service selama 1 tahun untuk motor SM SPORT',
                'image_path' => 'https://picsum.photos/seed/promo4/800/600',
                'link_url' => 'https://example.com/promo4',
                'button_text' => 'Registrasi',
                'sort_order' => 4,
                'type' => 'promo',
                'is_active' => true,
            ],
            [
                'title' => 'Trade-In Motor Bekas',
                'subtitle' => 'Tukarkan motor lama Anda dapatkan diskon tambahan hingga Rp 3.000.000',
                'image_path' => 'https://picsum.photos/seed/promo5/800/600',
                'link_url' => 'https://example.com/promo5',
                'button_text' => 'Cek Harga Motor Anda',
                'sort_order' => 5,
                'type' => 'promo',
                'is_active' => true,
            ],
        ];

        foreach ($promos as $data) {
            $banner = Banner::create($data);
            $this->command->info("Created: {$banner->title} (ID: {$banner->id})");
        }

        $this->command->info('---');
        $this->command->info('Total promo banners: ' . Banner::where('type', 'promo')->count());
    }
}

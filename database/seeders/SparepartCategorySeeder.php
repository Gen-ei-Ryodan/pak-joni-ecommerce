<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SparepartCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Permesinan' => ['Busi', 'Filter Oli', 'Kampas Kopling', 'Piston Kit', 'Gasket Set'],
            'Body' => ['Cover Body', 'Spakbor', 'Fairing', 'Visor', 'Handle Cover'],
            'Roda dan Suspensi' => ['Shockbreaker', 'Velg', 'Ban', 'Bearing Roda', 'Disc Brake'],
            'Casis' => ['Footstep', 'Standar Tengah', 'Swing Arm', 'Handle Bar', 'Triple Clamp'],
            'Elektrikal' => ['Lampu', 'Aki', 'Saklar', 'CDI', 'Spul', 'Kabel Body'],
        ];

        foreach ($categories as $group => $names) {
            foreach ($names as $name) {
                DB::table('part_categories')->insertOrIgnore([
                    'group' => $group,
                    'name' => $name,
                    'slug' => Str::slug($group.' '.$name),
                    'sort_order' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}

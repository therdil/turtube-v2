<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'belgesel' => 'Belgesel',
            'bilim' => 'Bilim',
            'diger' => 'Diğer',
            'egitim' => 'Eğitim',
            'eglence' => 'Eğlence',
            'film' => 'Film',
            'haber' => 'Haber',
            'muzik' => 'Müzik',
            'oyun' => 'Oyun',
            'spor' => 'Spor',
            'teknoloji' => 'Teknoloji',
            'yasam' => 'Yaşam',
        ];

        foreach ($categories as $slug => $name) {
            Category::firstOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'slug' => $slug,
                ]
            );
        }

        Cache::forget('navigation-categories-v4');
    }
}

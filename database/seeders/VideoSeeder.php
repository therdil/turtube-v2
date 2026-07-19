<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VideoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
{
    \App\Models\Video::create([
        'title' => 'TurTube Tanıtım Videosu',
        'description' => 'TurTube platformuna hoş geldiniz.',
        'thumbnail' => 'thumbnails/default.jpg',
        'video_path' => 'videos/sample.mp4',
        'channel_name' => 'TurTube',
        'views' => 1520,
        'duration' => 125,
    ]);

    \App\Models\Video::create([
        'title' => 'Laravel 13 Dersleri',
        'description' => 'Laravel öğrenmeye başlayın.',
        'thumbnail' => 'thumbnails/default.jpg',
        'video_path' => 'videos/sample.mp4',
        'channel_name' => 'Kod Akademi',
        'views' => 8432,
        'duration' => 980,
    ]);

    \App\Models\Video::create([
        'title' => 'PHP İpuçları',
        'description' => 'PHP geliştirme ipuçları.',
        'thumbnail' => 'thumbnails/default.jpg',
        'video_path' => 'videos/sample.mp4',
        'channel_name' => 'Yazılım Dünyası',
        'views' => 3250,
        'duration' => 420,
    ]);
}
}

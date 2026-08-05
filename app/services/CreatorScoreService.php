<?php

namespace App\Services;

use App\Models\Video;

class CreatorScoreService
{
    /**
     * Video kalite puanı hesapla
     */
    public function score(Video $video): int
    {
        $score = 0;

        // Başlık
        if (mb_strlen(trim($video->title)) >= 20) {
            $score += 20;
        }

        // Açıklama
        if (mb_strlen(strip_tags($video->description ?? '')) >= 100) {
            $score += 20;
        }

        // Thumbnail
        if (!empty($video->thumbnail)) {
            $score += 20;
        }

        // Kategori
        if (!empty($video->category_id)) {
            $score += 20;
        }

        // Yayın durumu
        if ($video->status === 'public') {
            $score += 20;
        }

        return $score;
    }

    /**
     * Performans rozeti
     */
    public function badge(Video $video): array
    {
        if ($video->views >= 100000) {
            return [
                'emoji' => '🔥',
                'text' => 'Trend',
                'color' => 'red',
            ];
        }

        if ($video->views >= 10000) {
            return [
                'emoji' => '⭐',
                'text' => 'Popüler',
                'color' => 'yellow',
            ];
        }

        if ($video->views >= 1000) {
            return [
                'emoji' => '🚀',
                'text' => 'Yükseliyor',
                'color' => 'blue',
            ];
        }

        if ($video->views == 0) {
            return [
                'emoji' => '💤',
                'text' => 'Pasif',
                'color' => 'gray',
            ];
        }

        return [
            'emoji' => '🆕',
            'text' => 'Yeni',
            'color' => 'green',
        ];
    }

    /**
     * İçerik üreticisine öneriler
     */
    public function suggestions(Video $video): array
    {
        $items = [];

        if (mb_strlen($video->title) < 20) {
            $items[] = 'Başlığı biraz daha açıklayıcı yap.';
        }

        if (mb_strlen(strip_tags($video->description ?? '')) < 100) {
            $items[] = 'Açıklama bölümünü genişlet.';
        }

        if (!$video->thumbnail) {
            $items[] = 'Özel thumbnail yükle.';
        }

        if (!$video->category_id) {
            $items[] = 'Kategori seç.';
        }

        if ($video->status !== 'public') {
            $items[] = 'Videoyu herkese açık yap.';
        }

        return $items;
    }
}

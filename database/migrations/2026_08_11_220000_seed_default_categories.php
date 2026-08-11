<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Add TurTube's default category catalogue without modifying user-created
     * categories or existing video assignments.
     */
    public function up(): void
    {
        $timestamp = now();

        DB::table('categories')->insertOrIgnore([
            ['name' => 'Belgesel', 'slug' => 'belgesel', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Bilim', 'slug' => 'bilim', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Diğer', 'slug' => 'diger', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Eğitim', 'slug' => 'egitim', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Eğlence', 'slug' => 'eglence', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Film', 'slug' => 'film', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Haber', 'slug' => 'haber', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Müzik', 'slug' => 'muzik', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Oyun', 'slug' => 'oyun', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Spor', 'slug' => 'spor', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Teknoloji', 'slug' => 'teknoloji', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Yaşam', 'slug' => 'yasam', 'created_at' => $timestamp, 'updated_at' => $timestamp],
        ]);

        Cache::forget('navigation-categories-v4');
    }

    public function down(): void
    {
        // Default categories are content data and must never be removed on rollback.
    }
};

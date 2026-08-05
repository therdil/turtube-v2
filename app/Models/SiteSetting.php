<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class SiteSetting extends Model
{
    protected $fillable = ['site_name', 'logo', 'banner', 'announcement', 'announcement_enabled', 'maintenance_mode', 'maintenance_message'];

    protected function casts(): array
    {
        return ['announcement_enabled' => 'boolean', 'maintenance_mode' => 'boolean'];
    }

    public static function current(): self
    {
        if (! Schema::hasTable('site_settings')) {
            return new self(['site_name' => 'TurTube', 'announcement_enabled' => false, 'maintenance_mode' => false]);
        }

        $cached = Cache::get('site-settings.current');
        if ($cached instanceof self) {
            return $cached;
        }

        Cache::forget('site-settings.current');
        $settings = static::query()->firstOrCreate(['id' => 1], ['site_name' => 'TurTube']);
        Cache::put('site-settings.current', $settings, now()->addHour());

        return $settings;
    }

    public static function forgetCurrent(): void
    {
        Cache::forget('site-settings.current');
    }
}

<?php

namespace App\Modules\DailyReport\Services;

use App\Models\LaporanImutAutoGenerationSetting;
use Illuminate\Support\Facades\Cache;

class CachedSettingsService
{
    /**
     * Cache setting in static/request-scoped variable
     */
    private static ?LaporanImutAutoGenerationSetting $cachedSetting = null;
    private static ?int $cachedBackDays = null;

    /**
     * Get cached LaporanImutAutoGenerationSetting instance
     * Reduces multiple DB queries to 1 per request
     */
    public static function getSetting(): ?LaporanImutAutoGenerationSetting
    {
        // Return from static cache if already loaded in this request
        if (self::$cachedSetting !== null) {
            return self::$cachedSetting;
        }

        // Load once from cache or database
        self::$cachedSetting = Cache::remember(
            'laporan_imut_auto_generation_setting_instance',
            3600, // 1 hour
            function () {
                return LaporanImutAutoGenerationSetting::getInstance();
            }
        );

        return self::$cachedSetting;
    }

    /**
     * Get cached back data entry days
     * Avoids repeated calls to getInstance()->getBackDataEntryDays()
     */
    public static function getBackDataEntryDays(): int
    {
        // Return from static cache if already loaded
        if (self::$cachedBackDays !== null) {
            return self::$cachedBackDays;
        }

        $setting = self::getSetting();
        self::$cachedBackDays = $setting ? $setting->getBackDataEntryDays() : 6;

        return self::$cachedBackDays;
    }

    /**
     * Clear cache (use after updating settings)
     */
    public static function clearCache(): void
    {
        self::$cachedSetting = null;
        self::$cachedBackDays = null;
        \Illuminate\Support\Facades\Cache::forget('laporan_imut_auto_generation_setting_instance');
    }
}

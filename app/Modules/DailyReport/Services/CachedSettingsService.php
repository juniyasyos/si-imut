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

        // Load once from static cache if already loaded in this request
        // LaporanImutAutoGenerationSetting::getInstance() handles its own application cache.
        self::$cachedSetting = LaporanImutAutoGenerationSetting::getInstance();

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
    }
}

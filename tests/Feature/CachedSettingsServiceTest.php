<?php

use App\Models\LaporanImutAutoGenerationSetting;
use App\Modules\DailyReport\Services\CachedSettingsService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('clears cache when setting is updated', function () {
    // Ensure fresh start
    Cache::flush();
    CachedSettingsService::clearCache();

    // Create initial setting
    $setting = LaporanImutAutoGenerationSetting::firstOrCreate([], LaporanImutAutoGenerationSetting::getDefaults());
    $setting->back_data_entry_duration = 5;
    $setting->save();

    // Fetch via CachedSettingsService
    $cached = CachedSettingsService::getSetting();
    expect($cached->back_data_entry_duration)->toBe(5);

    // Now simulate updating the setting in DB (like a user would via Filament)
    $setting->back_data_entry_duration = 10;
    $setting->save();

    // Fetch via CachedSettingsService again, but simulate a new request (clear static cache)
    // In a real scenario, this would be a new request, so static vars are null.
    $reflection = new \ReflectionClass(CachedSettingsService::class);
    $reflection->setStaticPropertyValue('cachedSetting', null);
    $reflection->setStaticPropertyValue('cachedBackDays', null);

    // The bug: it uses 'laporan_imut_auto_generation_setting_instance' from cache
    $newCached = CachedSettingsService::getSetting();
    
    // If the cache is cleared correctly, it should be 10. If the bug exists, it will be 5 and the test will fail.
    expect($newCached->back_data_entry_duration)->toBe(10);
});

<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FilamentResourceFixTest extends TestCase
{
    /**
     * Test that Filament resources load without initialization errors.
     */
    public function test_filament_resources_load_without_errors(): void
    {
        // Test that artisan commands work without resource initialization errors
        $this->artisan('route:list')
            ->assertExitCode(0);

        // Test that the admin panel is accessible
        $response = $this->get('/');
        $response->assertRedirect('/login');

        // Test that login page loads without errors
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    /**
     * Test that BusinessLogicServiceProvider is properly registered.
     */
    public function test_business_logic_service_provider_is_registered(): void
    {
        // Check if the service provider is loaded
        $providers = app()->getLoadedProviders();
        $this->assertArrayHasKey('App\Providers\BusinessLogicServiceProvider', $providers);
    }

    /**
     * Test that MediaCustomResource pages have correct resource references.
     */
    public function test_media_custom_resource_has_correct_references(): void
    {
        $createMediaPage = new \App\Filament\Resources\MediaCustomResource\Pages\CreateMediaCustom();
        $listMediaPage = new \App\Filament\Resources\MediaCustomResource\Pages\ListMediaCustom();

        // Using reflection to access protected static property
        $createReflection = new \ReflectionClass($createMediaPage);
        $createResourceProperty = $createReflection->getProperty('resource');
        $createResourceProperty->setAccessible(true);

        $listReflection = new \ReflectionClass($listMediaPage);
        $listResourceProperty = $listReflection->getProperty('resource');
        $listResourceProperty->setAccessible(true);

        // Both should reference MediaCustomResource, not MediaResource
        $this->assertEquals(
            \App\Filament\Resources\MediaCustomResource::class,
            $createResourceProperty->getValue()
        );

        $this->assertEquals(
            \App\Filament\Resources\MediaCustomResource::class,
            $listResourceProperty->getValue()
        );
    }
}

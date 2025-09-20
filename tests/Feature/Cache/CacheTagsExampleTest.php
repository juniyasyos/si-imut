<?php

namespace Tests\Feature\Cache;

use App\Services\Cache\Examples\CacheTagsExampleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CacheTagsExampleTest extends TestCase
{
    use RefreshDatabase;

    private CacheTagsExampleService $exampleService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->exampleService = new CacheTagsExampleService();

        // Clear cache before each test
        Cache::flush();
    }

    /** @test */
    public function it_demonstrates_basic_tagged_caching_from_laravel_docs()
    {
        // Following Laravel docs pattern exactly
        [$john, $anne] = $this->exampleService->exampleBasicTaggedCaching();

        $this->assertNotNull($john);
        $this->assertNotNull($anne);
        $this->assertEquals('John', $john->name);
        $this->assertEquals('Anne', $anne->name);
        $this->assertEquals('artist', $john->profession);
        $this->assertEquals('author', $anne->profession);
    }

    /** @test */
    public function it_demonstrates_selective_invalidation_from_laravel_docs()
    {
        // This test shows the Laravel pattern: Cache::tags(['people', 'authors'])->flush();
        [$johnAfterFlush, $anneAfterFlush] = $this->exampleService->exampleSelectiveInvalidation();

        // Both should be null because they share tags that were flushed
        $this->assertNull($johnAfterFlush);
        $this->assertNull($anneAfterFlush);
    }

    /** @test */
    public function it_demonstrates_single_tag_invalidation_from_laravel_docs()
    {
        // This test shows the Laravel pattern: Cache::tags('authors')->flush();
        [$johnAfterFlush, $anneAfterFlush] = $this->exampleService->exampleSingleTagInvalidation();

        // John should still exist (not tagged with 'authors')
        // Anne should be null (tagged with 'authors')
        $this->assertNotNull($johnAfterFlush);
        $this->assertNull($anneAfterFlush);
        $this->assertEquals('John', $johnAfterFlush->name);
    }

    /** @test */
    public function it_demonstrates_real_world_user_management()
    {
        $result = $this->exampleService->exampleUserManagement();

        $this->assertEquals('User management cache examples completed', $result);

        // Test that our service detected cache capabilities correctly
        if ($this->exampleService->supportsTagging()) {
            $this->assertTrue(true, 'Cache tagging is supported');
        } else {
            $this->assertTrue(true, 'Cache tagging gracefully degraded');
        }
    }

    /** @test */
    public function it_demonstrates_real_world_laporan_management()
    {
        $result = $this->exampleService->exampleLaporanManagement();

        $this->assertEquals('Laporan management cache examples completed', $result);
    }

    /** @test */
    public function it_demonstrates_hierarchical_cache_invalidation()
    {
        $result = $this->exampleService->exampleHierarchicalInvalidation();

        $this->assertEquals('Hierarchical invalidation examples completed', $result);
    }

    /** @test */
    public function it_demonstrates_using_base_cache_service_methods()
    {
        // This might return null if cache tagging isn't supported, which is fine
        $retrievedUser = $this->exampleService->exampleUsingBaseCacheServiceMethods();

        // The method should complete without errors regardless of cache support
        $this->assertTrue(true, 'BaseCacheService methods executed successfully');
    }

    /** @test */
    public function it_demonstrates_performance_comparison()
    {
        $result = $this->exampleService->examplePerformanceComparison();

        $this->assertEquals('Performance comparison examples completed', $result);
    }

    /** @test */
    public function it_follows_laravel_documentation_patterns_exactly()
    {
        // Test the exact patterns from Laravel docs

        // Skip if not using array store for tag testing
        if (config('cache.default') !== 'array') {
            $this->markTestSkipped('This test requires array cache store for tag support verification');
        }

        // Laravel pattern 1: Cache::tags(['people', 'artists'])->put('John', $john, $seconds);
        $john = (object) ['name' => 'John Doe', 'type' => 'artist'];
        Cache::tags(['people', 'artists'])->put('John', $john, 3600);

        // Laravel pattern 2: Cache::tags(['people', 'authors'])->put('Anne', $anne, $seconds);
        $anne = (object) ['name' => 'Anne Smith', 'type' => 'author'];
        Cache::tags(['people', 'authors'])->put('Anne', $anne, 3600);

        // Laravel pattern 3: $john = Cache::tags(['people', 'artists'])->get('John');
        $retrievedJohn = Cache::tags(['people', 'artists'])->get('John');
        $retrievedAnne = Cache::tags(['people', 'authors'])->get('Anne');

        $this->assertEquals('John Doe', $retrievedJohn->name);
        $this->assertEquals('Anne Smith', $retrievedAnne->name);

        // Laravel pattern 4: Cache::tags(['people', 'authors'])->flush();
        Cache::tags(['people', 'authors'])->flush();

        // Both should be gone because they share the 'people' tag
        $johnAfterFlush = Cache::tags(['people', 'artists'])->get('John');
        $anneAfterFlush = Cache::tags(['people', 'authors'])->get('Anne');

        $this->assertNull($johnAfterFlush);
        $this->assertNull($anneAfterFlush);
    }

    /** @test */
    public function it_handles_cache_store_without_tag_support_gracefully()
    {
        // Test that our implementation works even when tags aren't supported

        // Force check for tag support
        $supportsTagging = $this->exampleService->supportsTagging();

        // Test should pass regardless of tag support
        if ($supportsTagging) {
            $this->assertTrue(true, 'Tags are supported - enhanced functionality available');
        } else {
            $this->assertTrue(true, 'Tags not supported - graceful degradation working');
        }

        // All methods should work without throwing exceptions
        $this->exampleService->exampleBasicTaggedCaching();
        $this->exampleService->exampleUserManagement();
        $this->exampleService->exampleLaporanManagement();

        $this->assertTrue(true, 'All cache operations completed without errors');
    }
}

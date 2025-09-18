<?php

use App\Services\LaporanImut\LaporanImutQueryService;
use App\Repositories\Interfaces\LaporanImutRepositoryInterface;

it('can construct service with repository dependency', function () {
    $mockRepository = mock(LaporanImutRepositoryInterface::class);
    $service = new LaporanImutQueryService($mockRepository);

    expect($service)->toBeInstanceOf(LaporanImutQueryService::class);
});

it('service has proper dependency injection structure', function () {
    $mockRepository = mock(LaporanImutRepositoryInterface::class);
    $service = new LaporanImutQueryService($mockRepository);

    $reflection = new ReflectionClass($service);
    $constructor = $reflection->getConstructor();

    expect($constructor->getParameters())->toHaveCount(1);
    expect($constructor->getParameters()[0]->getName())->toBe('laporanRepository');
});

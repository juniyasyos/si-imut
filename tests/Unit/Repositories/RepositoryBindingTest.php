<?php

use App\Repositories\ImutDataRepository;
use App\Repositories\ImutProfileRepository;
use App\Repositories\LaporanImutRepository;
use App\Repositories\Interfaces\ImutDataRepositoryInterface;
use App\Repositories\Interfaces\ImutProfileRepositoryInterface;
use App\Repositories\Interfaces\LaporanImutRepositoryInterface;

describe('Repository Service Bindings', function () {
    it('binds ImutDataRepositoryInterface correctly', function () {
        $repository = app(ImutDataRepositoryInterface::class);

        expect($repository)->toBeInstanceOf(ImutDataRepository::class);
    });

    it('binds LaporanImutRepositoryInterface correctly', function () {
        $repository = app(LaporanImutRepositoryInterface::class);

        expect($repository)->toBeInstanceOf(LaporanImutRepository::class);
    });

    it('binds ImutProfileRepositoryInterface correctly', function () {
        $repository = app(ImutProfileRepositoryInterface::class);

        expect($repository)->toBeInstanceOf(ImutProfileRepository::class);
    });

    it('can resolve all repository interfaces', function () {
        $interfaces = [
            ImutDataRepositoryInterface::class,
            LaporanImutRepositoryInterface::class,
            ImutProfileRepositoryInterface::class,
        ];

        foreach ($interfaces as $interface) {
            $repository = app($interface);
            expect($repository)->toBeObject();
        }
    });

    it('returns singleton instances', function () {
        $first = app(ImutDataRepositoryInterface::class);
        $second = app(ImutDataRepositoryInterface::class);

        expect($first)->toBe($second);
    });
});

<?php

use App\Repositories\Interfaces\ImutDataRepositoryInterface;
use App\Repositories\ImutDataRepository;

describe('Simple Repository Binding Test', function () {
    beforeEach(function () {
        // Manually bind the repository for testing
        app()->bind(
            ImutDataRepositoryInterface::class,
            ImutDataRepository::class
        );
    });

    it('can resolve repository from container', function () {
        $repository = app(ImutDataRepositoryInterface::class);

        expect($repository)->toBeInstanceOf(ImutDataRepository::class);
    });

    it('can instantiate repository directly', function () {
        $model = new \App\Models\ImutData();
        $repository = new \App\Repositories\ImutDataRepository($model);

        expect($repository)->toBeInstanceOf(\App\Repositories\ImutDataRepository::class);
    });
});

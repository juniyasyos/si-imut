<?php

use App\Services\Filament\ImutDataFilamentService;
use App\Services\ImutDataService;
use App\Models\ImutData;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

beforeEach(function () {
    $this->imutDataService = Mockery::mock(ImutDataService::class);
    $this->filamentService = new ImutDataFilamentService($this->imutDataService);
});

afterEach(function () {
    Mockery::close();
});

describe('ImutDataFilamentService', function () {
    it('can get globally searchable attributes', function () {
        $attributes = $this->filamentService->getGloballySearchableAttributes();

        expect($attributes)->toBe(['title']);
    });

    it('can get category badge color', function () {
        $color1 = $this->filamentService->getCategoryBadgeColor(1);
        $color2 = $this->filamentService->getCategoryBadgeColor(2);

        expect($color1)->toBe('success'); // colors[1]
        expect($color2)->toBe('warning'); // colors[2]
    });

    it('can check toggle status permission', function () {
        Gate::shouldReceive('any')
            ->with(['update_imut::data'])
            ->once()
            ->andReturn(true);

        $canToggle = $this->filamentService->canToggleStatus();

        expect($canToggle)->toBe(true);
    });

    it('can update imut data', function () {
        $updateData = ['title' => 'Updated Title'];

        $this->imutDataService
            ->shouldReceive('updateImutData')
            ->with(1, $updateData)
            ->once()
            ->andReturn(true);

        $result = $this->filamentService->updateImutDataWithUnitKerja(1, $updateData);

        expect($result)->toBe(true);
    });
});

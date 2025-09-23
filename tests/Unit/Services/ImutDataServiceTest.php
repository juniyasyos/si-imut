<?php

use App\Services\ImutDataService;
use App\Repositories\Interfaces\ImutDataRepositoryInterface;
use App\Models\ImutData;
use Illuminate\Support\Facades\Auth;

beforeEach(function () {
    // Mock repository tanpa database
    $this->repository = Mockery::mock(ImutDataRepositoryInterface::class);
    $this->service = new ImutDataService($this->repository);
});

afterEach(function () {
    Mockery::close();
});

describe('ImutDataService', function () {
    it('can get all imut data', function () {
        $expectedData = collect([]);

        $this->repository
            ->shouldReceive('all')
            ->once()
            ->andReturn($expectedData);

        $result = $this->service->getAllImutData();

        expect($result)->toBe($expectedData);
    });

    it('can find imut data by id', function () {
        $imutData = Mockery::mock(ImutData::class);

        $this->repository
            ->shouldReceive('find')
            ->with(1)
            ->once()
            ->andReturn($imutData);

        $result = $this->service->findImutData(1);

        expect($result)->toBe($imutData);
    });

    it('can create imut data with defaults', function () {
        $inputData = [
            'title' => 'Test Title',
            'description' => 'Test Description'
        ];

        $expectedData = [
            'title' => 'Test Title',
            'description' => 'Test Description',
            'status' => true,
            'created_by' => 1
        ];

        $imutData = Mockery::mock(ImutData::class);

        // Mock Auth facade
        Auth::shouldReceive('id')->once()->andReturn(1);

        $this->repository
            ->shouldReceive('create')
            ->with($expectedData)
            ->once()
            ->andReturn($imutData);

        $result = $this->service->createImutData($inputData);

        expect($result)->toBe($imutData);
    });

    it('can update imut data', function () {
        $updateData = ['title' => 'Updated Title'];

        $this->repository
            ->shouldReceive('update')
            ->with(1, $updateData)
            ->once()
            ->andReturn(true);

        $result = $this->service->updateImutData(1, $updateData);

        expect($result)->toBe(true);
    });

    it('can delete imut data', function () {
        $this->repository
            ->shouldReceive('delete')
            ->with(1)
            ->once()
            ->andReturn(true);

        $result = $this->service->deleteImutData(1);

        expect($result)->toBe(true);
    });
});

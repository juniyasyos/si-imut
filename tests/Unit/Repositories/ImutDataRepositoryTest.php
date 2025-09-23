<?php

use App\Repositories\ImutDataRepository;
use App\Models\ImutData;

beforeEach(function () {
    $this->model = Mockery::mock(ImutData::class);
    $this->repository = new ImutDataRepository($this->model);
});

afterEach(function () {
    Mockery::close();
});

describe('ImutDataRepository', function () {
    it('can get all imut data with categories', function () {
        $expectedData = collect([]);

        $this->model
            ->shouldReceive('with')
            ->with('categories')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('get')
            ->once()
            ->andReturn($expectedData);

        $result = $this->repository->all();

        expect($result)->toBe($expectedData);
    });

    it('can find imut data by id with categories', function () {
        $imutData = Mockery::mock(ImutData::class);

        $this->model
            ->shouldReceive('with')
            ->with('categories')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('find')
            ->with(1)
            ->once()
            ->andReturn($imutData);

        $result = $this->repository->find(1);

        expect($result)->toBe($imutData);
    });

    it('can create imut data', function () {
        $data = ['title' => 'Test Title'];
        $imutData = Mockery::mock(ImutData::class);

        $this->model
            ->shouldReceive('create')
            ->with($data)
            ->once()
            ->andReturn($imutData);

        $result = $this->repository->create($data);

        expect($result)->toBe($imutData);
    });

    it('can update existing imut data', function () {
        $imutData = Mockery::mock(ImutData::class);
        $updateData = ['title' => 'Updated Title'];

        $this->model
            ->shouldReceive('find')
            ->with(1)
            ->once()
            ->andReturn($imutData);

        $imutData
            ->shouldReceive('update')
            ->with($updateData)
            ->once()
            ->andReturn(true);

        $result = $this->repository->update(1, $updateData);

        expect($result)->toBe(true);
    });

    it('returns false when updating non-existent imut data', function () {
        $this->model
            ->shouldReceive('find')
            ->with(999)
            ->once()
            ->andReturn(null);

        $result = $this->repository->update(999, ['title' => 'Updated']);

        expect($result)->toBe(false);
    });

    it('can delete existing imut data', function () {
        $imutData = Mockery::mock(ImutData::class);

        $this->model
            ->shouldReceive('find')
            ->with(1)
            ->once()
            ->andReturn($imutData);

        $imutData
            ->shouldReceive('delete')
            ->once()
            ->andReturn(true);

        $result = $this->repository->delete(1);

        expect($result)->toBe(true);
    });

    it('returns false when deleting non-existent imut data', function () {
        $this->model
            ->shouldReceive('find')
            ->with(999)
            ->once()
            ->andReturn(null);

        $result = $this->repository->delete(999);

        expect($result)->toBe(false);
    });

    it('can get active imut data', function () {
        $activeData = collect([]);

        $this->model
            ->shouldReceive('with')
            ->with('categories')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('where')
            ->with('status', true)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('get')
            ->once()
            ->andReturn($activeData);

        $result = $this->repository->getActive();

        expect($result)->toBe($activeData);
    });
});

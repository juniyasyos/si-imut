<?php

use App\Factories\ImutProfileFactory;
use App\Models\ImutData;
use App\Models\ImutProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->factory = app(ImutProfileFactory::class);
    $this->user = User::factory()->create();
    $this->imutData = ImutData::factory()->create();
    $this->actingAs($this->user);
});

describe('ImutProfileFactory', function () {
    it('can create profile with required attributes', function () {
        $profile = $this->factory->create([
            'imut_data_id' => $this->imutData->id,
            'version' => 'v1.0',
        ]);

        expect($profile)->toBeInstanceOf(ImutProfile::class)
            ->and($profile->imut_data_id)->toBe($this->imutData->id)
            ->and($profile->version)->toBe('v1.0')
            ->and($profile->target_value)->toBe(80)
            ->and($profile->indicator_type)->toBe('process');
    });

    it('throws exception when ImutData ID is missing', function () {
        expect(fn() => $this->factory->create(['version' => 'v1.0']))
            ->toThrow(InvalidArgumentException::class, 'ImutData ID is required');
    });

    it('throws exception when ImutData does not exist', function () {
        expect(fn() => $this->factory->create([
            'imut_data_id' => 999999,
            'version' => 'v1.0',
        ]))->toThrow(InvalidArgumentException::class, 'ImutData not found');
    });

    it('can create profile with template', function () {
        $profile = $this->factory->createWithTemplate(
            $this->imutData->id,
            'v1.0',
            'safety'
        );

        expect($profile->quality_dimension)->toBe('Keselamatan Pasien')
            ->and($profile->target_value)->toBe(95)
            ->and($profile->objective)->toBe('Meningkatkan keselamatan pasien');
    });

    it('can create profile for specific indicator type', function () {
        $profile = $this->factory->createForIndicatorType(
            $this->imutData->id,
            'v1.0',
            'outcome'
        );

        expect($profile->indicator_type)->toBe('outcome')
            ->and($profile->target_value)->toBe(80)
            ->and($profile->numerator_formula)->toBe('Jumlah outcome yang sesuai harapan');
    });

    it('applies default values correctly', function () {
        $profile = $this->factory->create([
            'imut_data_id' => $this->imutData->id,
            'version' => 'v1.0',
        ]);

        expect($profile->rationale)->toBe('Rasional profil IMUT standar')
            ->and($profile->target_operator)->toBe('>=')
            ->and($profile->data_collection_frequency)->toBe('Bulanan')
            ->and($profile->responsible_person)->toBe($this->user->name);
    });

    it('can override default values', function () {
        $profile = $this->factory->create([
            'imut_data_id' => $this->imutData->id,
            'version' => 'v1.0',
            'target_value' => 95,
            'target_operator' => '<=',
            'data_collection_frequency' => 'Mingguan',
        ]);

        expect($profile->target_value)->toBe(95)
            ->and($profile->target_operator)->toBe('<=')
            ->and($profile->data_collection_frequency)->toBe('Mingguan');
    });
});

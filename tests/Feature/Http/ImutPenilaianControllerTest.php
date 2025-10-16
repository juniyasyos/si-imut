<?php

use App\Domains\Imut\Actions\SubmitImutPenilaian;
use App\Domains\Imut\Events\ImutPenilaianSubmitted;
use App\Domains\Imut\Models\ImutPenilaian;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

it('persists penilaian updates through the submit action and dispatches event', function () {
    Event::fake();

    $penilaian = ImutPenilaian::factory()->create([
        'analysis' => 'Old analysis',
        'recommendations' => 'Old recommendation',
        'numerator_value' => 10,
        'denominator_value' => 20,
    ]);

    $payload = [
        'analysis' => 'Updated analysis',
        'recommendations' => 'Fresh recommendation',
        'numerator_value' => 30,
        'denominator_value' => 40,
    ];

    $result = app(SubmitImutPenilaian::class)->execute($penilaian, $payload);

    expect($result->analysis)->toBe('Updated analysis')
        ->and($result->recommendations)->toBe('Fresh recommendation')
        ->and($result->numerator_value)->toBe(30.0)
        ->and($result->denominator_value)->toBe(40.0);

    Event::assertDispatched(ImutPenilaianSubmitted::class, fn($event) => $event->penilaian->is($penilaian));
});

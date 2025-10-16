<?php

use App\Support\Auth\Ability;

it('formats ability strings consistently for resource checks', function () {
    expect(Ability::resource(Ability::View, 'imut::category'))->toBe('view_imut::category')
        ->and(Ability::resource(Ability::View, 'imut::category', 'any'))->toBe('view_any_imut::category')
        ->and(Ability::resource(Ability::Update, 'imut::penilaian', 'profile', 'penilaian'))
            ->toBe('update_profile_penilaian_imut::penilaian')
        ->and(Ability::resource(Ability::Delete, 'unit::kerja', 'any'))->toBe('delete_any_unit::kerja');
});

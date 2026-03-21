<?php

return [
    'model' => [
        'unit_kerja' => \Juniyasyos\ManageUnitKerja\Models\UnitKerja::class,
        'user' => \App\Models\User::class,
    ],

    'filament' => [
        'active' => true,
        'resources' => [
            \Juniyasyos\ManageUnitKerja\Filament\Resources\UnitKerjaResource::class,
        ],
    ],

    'navigation_sort' => 0,
];

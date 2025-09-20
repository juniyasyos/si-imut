<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * LaporanImut Business Logic Facade
 *
 * Provides simple access to LaporanImut operations
 *
 * @method static \App\Models\LaporanImut create(array $data)
 * @method static \App\Models\LaporanImut update(int $id, array $data)
 * @method static bool delete(int $id)
 * @method static \Illuminate\Contracts\Pagination\LengthAwarePaginator list(array $filters = [], array $sorting = [], int $page = 1, int $perPage = 15)
 * @method static array getWidgetData(array $parameters = [])
 * @method static array getChartData(array $parameters = [])
 */
class LaporanImutFacade extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'laporan-imut-business-logic';
    }
}

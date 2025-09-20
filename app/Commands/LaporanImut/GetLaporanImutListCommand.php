<?php

namespace App\Commands\LaporanImut;

use App\Commands\BaseCommand;
use App\Commands\Contracts\QueryCommandInterface;
use App\Repositories\Interfaces\LaporanImutRepositoryInterface;
use App\Services\LaporanImut\LaporanImutCacheService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

/**
 * Get LaporanImut List Command
 *
 * Handles querying and filtering of LaporanImut entities
 */
class GetLaporanImutListCommand extends BaseCommand implements QueryCommandInterface
{
    protected array $filters = [];
    protected array $sorting = ['field' => 'created_at', 'direction' => 'desc'];
    protected array $pagination = ['page' => 1, 'per_page' => 15];
    protected bool $useCache = true;

    public function __construct(
        private LaporanImutRepositoryInterface $repository,
        private LaporanImutCacheService $cacheService
    ) {
        // No validation rules needed for queries
    }

    /**
     * Set query parameters
     */
    public function setParameters(array $parameters): self
    {
        $this->data = $parameters;

        // Extract specific parameters
        if (isset($parameters['use_cache'])) {
            $this->useCache = (bool) $parameters['use_cache'];
        }

        return $this;
    }

    /**
     * Add filter to the query
     */
    public function addFilter(string $field, $value, string $operator = '='): self
    {
        $this->filters[] = compact('field', 'value', 'operator');
        return $this;
    }

    /**
     * Set pagination parameters
     */
    public function paginate(int $page = 1, int $perPage = 15): self
    {
        $this->pagination = ['page' => $page, 'per_page' => $perPage];
        return $this;
    }

    /**
     * Set sorting parameters
     */
    public function sortBy(string $field, string $direction = 'asc'): self
    {
        $this->sorting = ['field' => $field, 'direction' => $direction];
        return $this;
    }

    /**
     * Execute the query command
     *
     * @return LengthAwarePaginator|Collection
     */
    public function execute()
    {
        $cacheKey = $this->generateCacheKey();

        if ($this->useCache) {
            return $this->cacheService->remember(
                $cacheKey,
                1800, // 30 minutes in seconds
                fn() => $this->executeQuery()
            );
        }

        return $this->executeQuery();
    }

    /**
     * Execute the actual query
     */
    private function executeQuery()
    {
        // Start with all records
        $collection = $this->repository->all();

        // Apply filters (note: this is less efficient than database-level filtering)
        foreach ($this->filters as $filter) {
            $collection = $collection->filter(function ($item) use ($filter) {
                $value = data_get($item, $filter['field']);

                return match ($filter['operator']) {
                    '=' => $value == $filter['value'],
                    '!=' => $value != $filter['value'],
                    '>' => $value > $filter['value'],
                    '<' => $value < $filter['value'],
                    '>=' => $value >= $filter['value'],
                    '<=' => $value <= $filter['value'],
                    'like' => str_contains(strtolower($value), strtolower($filter['value'])),
                    default => $value == $filter['value'],
                };
            });
        }

        // Apply sorting
        $collection = $collection->sortBy(
            $this->sorting['field'],
            SORT_REGULAR,
            $this->sorting['direction'] === 'desc'
        );

        // Handle pagination manually for collection
        if ($this->pagination['per_page'] > 0) {
            $page = $this->pagination['page'];
            $perPage = $this->pagination['per_page'];
            $total = $collection->count();
            $offset = ($page - 1) * $perPage;

            $items = $collection->slice($offset, $perPage)->values();

            return new \Illuminate\Pagination\LengthAwarePaginator(
                $items,
                $total,
                $perPage,
                $page,
                [
                    'path' => request()->url(),
                    'pageName' => 'page',
                ]
            );
        }

        return $collection;
    }

    /**
     * Generate cache key for the query
     */
    private function generateCacheKey(): string
    {
        $key = 'laporan_imut_list';
        $key .= '_filters_' . md5(serialize($this->filters));
        $key .= '_sort_' . $this->sorting['field'] . '_' . $this->sorting['direction'];
        $key .= '_page_' . $this->pagination['page'] . '_' . $this->pagination['per_page'];

        return $key;
    }

    /**
     * Get paginated list with filters
     */
    public static function getPaginatedList(array $filters = [], array $sorting = [], int $page = 1, int $perPage = 15)
    {
        $command = app(self::class);

        foreach ($filters as $filter) {
            $command->addFilter($filter['field'], $filter['value'], $filter['operator'] ?? '=');
        }

        if (!empty($sorting)) {
            $command->sortBy($sorting['field'], $sorting['direction'] ?? 'asc');
        }

        return $command->paginate($page, $perPage)->execute();
    }
}

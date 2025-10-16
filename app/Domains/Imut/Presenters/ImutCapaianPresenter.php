<?php

namespace App\Domains\Imut\Presenters;

class ImutCapaianPresenter
{
    /**
     * Default palette used when no override is provided.
     *
     * @var array<int, string>
     */
    private array $defaultColors = [
        '#3B82F6',
        '#EF4444',
        '#10B981',
        '#F59E0B',
        '#8B5CF6',
        '#EC4899',
        '#06B6D4',
        '#84CC16',
    ];

    /**
     * Present dataset into Apex chart ready series structures.
     *
     * @param  array<int, array{name: string, values: array<int, float|int>}>  $dataset
     * @param  array<string, string>  $colorOverrides
     */
    public function present(array $dataset, array $colorOverrides = []): array
    {
        $index = 0;

        return array_map(function (array $series) use (&$index, $colorOverrides) {
            $name = $series['name'];
            $color = $colorOverrides[$name] ?? $this->defaultColors[$index % count($this->defaultColors)];

            $index++;

            return [
                'name' => $name,
                'data' => $series['values'],
                'color' => $color,
            ];
        }, $dataset);
    }
}

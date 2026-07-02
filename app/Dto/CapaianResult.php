<?php

namespace App\Dto;

/**
 * DTO untuk hasil kalkulasi capaian IMUT per periode.
 * Menampung data chart (per bulan per kategori) dan statistik ringkasan.
 */
class CapaianResult
{
    public function __construct(
        /** @var array<string, array<int, float>> Chart data: [categoryName => [month => percentage]] */
        public readonly array $chartData,

        /** @var array Statistik ringkasan untuk footer widget */
        public readonly array $statistikData,
    ) {}
}

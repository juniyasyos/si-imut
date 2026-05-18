<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Service untuk parsing periode dan tanggal
 * Menangani berbagai format periode input
 */
class PeriodParserService
{
    /**
     * Parse periode string ke dalam date range
     * Supported formats:
     * - YYYY                : whole year
     * - YYYY-MM             : specific month
     * - YYYY-Q[1-4]         : quarter
     * - YYYY-S[1-2]         : semester
     * - YYYY-MM,YYYY-MM     : custom range (start,end)
     *
     * @param string $periode
     * @return array{startDate: Carbon, endDate: Carbon}
     * @throws \Exception
     */
    public function parse(string $periode): array
    {
        $startDate = null;
        $endDate = null;

        if (preg_match('/^\d{4}$/', $periode)) {
            // full year
            $year = intval($periode);
            $startDate = Carbon::createFromDate($year, 1, 1)->startOfDay();
            $endDate = Carbon::createFromDate($year, 12, 31)->endOfDay();
        } elseif (preg_match('/^(\d{4})-(\d{2})$/', $periode, $m)) {
            // month
            [$year, $month] = [intval($m[1]), intval($m[2])];
            if ($month < 1 || $month > 12) {
                throw new \InvalidArgumentException('Bulan tidak valid dalam parameter periode');
            }
            $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $endDate = (clone $startDate)->endOfMonth();
        } elseif (preg_match('/^(\d{4})-Q([1-4])$/', $periode, $m)) {
            // quarter
            $year = intval($m[1]);
            $quarter = intval($m[2]);
            $monthStart = ($quarter - 1) * 3 + 1;
            $startDate = Carbon::createFromDate($year, $monthStart, 1)->startOfMonth();
            $endDate = (clone $startDate)->addMonths(2)->endOfMonth();
        } elseif (preg_match('/^(\d{4})-S([12])$/', $periode, $m)) {
            // semester
            $year = intval($m[1]);
            $sem = intval($m[2]);
            $monthStart = $sem === 1 ? 1 : 7;
            $startDate = Carbon::createFromDate($year, $monthStart, 1)->startOfMonth();
            $endDate = (clone $startDate)->addMonths(5)->endOfMonth();
        } elseif (strpos($periode, ',') !== false) {
            // custom range
            [$p1, $p2] = explode(',', $periode, 2);
            try {
                $startDate = Carbon::parse($p1)->startOfMonth();
                $endDate = Carbon::parse($p2)->endOfMonth();
            } catch (\Exception $e) {
                throw new \InvalidArgumentException('Format custom periode tidak valid: ' . $e->getMessage());
            }
        }

        if (!$startDate || !$endDate) {
            throw new \InvalidArgumentException('Parameter periode tidak valid');
        }

        return [
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];
    }

    /**
     * Generate list of months antara start dan end date
     */
    public function generateMonthList(Carbon $startDate, Carbon $endDate): Collection
    {
        $months = [];
        $cursor = $startDate->copy();

        while ($cursor->lte($endDate)) {
            $months[] = $cursor->format('Y-m');
            $cursor->addMonth();
        }

        return collect($months)->map(function ($m) {
            return [
                'value' => $m,
                'label' => Carbon::parse($m . '-01')->translatedFormat('M Y'),
            ];
        });
    }
}

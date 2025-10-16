<?php

namespace App\Domains\Reporting\Presenters;

class LaporanCardPresenter
{
    /**
     * Build the dashboard stat configuration payload.
     */
    public function present(array $payload): array
    {
        return [
            [
                'key' => 'tercapai',
                'label' => 'Indikator Tercapai',
                'description' => $this->generateTrendDescription(
                    array_column($payload['chart'] ?? [], 'tercapai'),
                    'indikator'
                ),
                'descriptionIcon' => 'heroicon-o-arrow-trending-up',
                'icon' => $this->resolveIcon($payload['tercapai'] ?? 0, $payload['totalIndikator'] ?? 1),
                'color' => fn(array $data) => $this->resolvePercentageColor(
                    $data['tercapai'] ?? 0,
                    $data['totalIndikator'] ?? 1
                ),
                'chart' => 'tercapai',
                'format' => fn($value) => "{$value} / " . ($payload['totalIndikator'] ?? 1),
            ],
            [
                'key' => 'unitMelapor',
                'label' => 'Unit Aktif Melapor',
                'description' => $this->generateTrendDescription(
                    array_column($payload['chart'] ?? [], 'unitMelapor'),
                    'unit'
                ),
                'descriptionIcon' => 'heroicon-o-user-plus',
                'icon' => 'heroicon-o-user-group',
                'color' => fn(array $data) => $this->resolvePercentageColor(
                    $data['unitMelapor'] ?? 0,
                    $data['totalUnit'] ?? 1
                ),
                'chart' => 'unitMelapor',
                'format' => fn($value) => "{$value} / " . ($payload['totalUnit'] ?? 1) . ' Unit',
            ],
            [
                'key' => 'belumDinilai',
                'label' => 'Indikator Belum Dinilai',
                'description' => $this->generateTrendDescription(
                    array_column($payload['chart'] ?? [], 'belumDinilai'),
                    'indikator belum dinilai',
                    true
                ),
                'descriptionIcon' => 'heroicon-o-pencil-square',
                'icon' => 'heroicon-o-clock',
                'color' => fn(array $data) => ($data['belumDinilai'] ?? 0) > 5 ? 'danger' : 'warning',
                'chart' => 'belumDinilai',
            ],
        ];
    }

    private function generateTrendDescription(array $chart, string $unit = '', bool $inverse = false): string
    {
        $count = count($chart);

        if ($count < 2) {
            return 'Data belum cukup untuk menganalisis tren.';
        }

        $latest = $chart[$count - 1];
        $previous = $chart[$count - 2];
        $diff = $latest - $previous;
        $abs = abs($diff);

        if ($diff === 0) {
            return match ($unit) {
                'indikator' => 'Capaian indikator stabil dalam dua periode terakhir.',
                'unit' => 'Jumlah unit pelapor tidak berubah.',
                'indikator belum dinilai' => 'Tidak ada perubahan pada indikator yang belum dinilai.',
                default => ucfirst($unit) . ' stabil tanpa perubahan.',
            };
        }

        if ($inverse) {
            return $diff > 0
                ? ucfirst($unit) . " meningkat sebesar {$abs} dibandingkan periode sebelumnya — arah negatif."
                : ucfirst($unit) . " menurun sebesar {$abs} — ini pertanda positif.";
        }

        return match ($unit) {
            'indikator' => $diff > 0
                ? "Jumlah indikator tercapai meningkat {$abs} dibandingkan periode sebelumnya."
                : "Jumlah indikator tercapai menurun {$abs} dari periode sebelumnya.",
            'unit' => $diff > 0
                ? "{$abs} unit baru mulai melapor dibandingkan sebelumnya."
                : "{$abs} unit tidak melapor dibandingkan periode sebelumnya.",
            'indikator belum dinilai' => $diff > 0
                ? "{$abs} indikator tambahan belum dinilai — perlu perhatian."
                : "{$abs} indikator telah dinilai sejak periode sebelumnya — perkembangan positif.",
            default => ucfirst($unit) . ($diff > 0
                ? " naik sebesar {$abs} dibanding sebelumnya."
                : " turun sebesar {$abs} dari sebelumnya."),
        };
    }

    private function resolveIcon(int $value, int $total): string
    {
        $percentage = $total ? round($value / max($total, 1) * 100) : 0;

        return $percentage >= 80 ? 'heroicon-o-check-circle' : 'heroicon-o-adjustments-vertical';
    }

    private function resolvePercentageColor(int $value, int $total): string
    {
        $percentage = $total ? round($value / max($total, 1) * 100) : 0;

        return match (true) {
            $percentage >= 80 => 'success',
            $percentage >= 50 => 'warning',
            default => 'danger',
        };
    }
}

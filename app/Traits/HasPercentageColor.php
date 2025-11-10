<?php

namespace App\Traits;

/**
 * Trait HasPercentageColor
 *
 * Provides percentage color calculation based on IMUT standards.
 * Used for displaying color-coded percentages in tables.
 *
 * @package App\Traits
 */
trait HasPercentageColor
{
    /**
     * Get color for percentage based on IMUT standard compliance
     *
     * @param object $record Record containing percentage, imut_standard, and imut_standard_type_operator
     * @return string|null Color name (success, warning, danger) or null if validation fails
     */
    protected function getPercentageColor($record): ?string
    {
        if (!is_numeric($record->percentage) || !is_numeric($record->imut_standard)) {
            return null;
        }

        // Check if meets standard (green)
        $meetsStandard = match ($record->imut_standard_type_operator) {
            '=' => $record->percentage == $record->imut_standard,
            '>=' => $record->percentage >= $record->imut_standard,
            '<=' => $record->percentage <= $record->imut_standard,
            '<' => $record->percentage < $record->imut_standard,
            '>' => $record->percentage > $record->imut_standard,
            default => false,
        };

        if ($meetsStandard) {
            return 'success';
        }

        // Check if within 80% threshold (yellow)
        $meetsThreshold = match ($record->imut_standard_type_operator) {
            '=' => $record->percentage == ($record->imut_standard * 0.8),
            '>=' => $record->percentage >= ($record->imut_standard * 0.8),
            '<=' => $record->percentage <= ($record->imut_standard * 1.2),
            '<' => $record->percentage < ($record->imut_standard * 1.2),
            '>' => $record->percentage > ($record->imut_standard * 0.8),
            default => false,
        };

        return $meetsThreshold ? 'warning' : 'danger';
    }

    /**
     * Get color for IMUT category badge
     *
     * @param int $categoryId Category ID
     * @return string Color name for the badge
     */
    protected function getCategoryColor(int $categoryId): string
    {
        $colors = ['primary', 'success', 'warning', 'danger', 'info', 'gray'];
        return $colors[$categoryId % count($colors)];
    }
}

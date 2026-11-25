<?php

namespace App\Exports\Concerns;

use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;

/**
 * Custom value binder untuk export Excel:
 * mencegah string berawalan '=' ditafsirkan sebagai formula Excel
 * dan membersihkan karakter kontrol ASCII non-XML.
 */
trait HasSafeExcelValueBinder
{
    private ?StringValueBinder $safeValueBinder = null;

    public function bindValue(Cell $cell, $value): bool
    {
        if (is_string($value)) {
            $cleaned = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $value);
            if (is_string($cleaned)) {
                $value = $cleaned;
            }
        }

        if ($this->safeValueBinder === null) {
            $this->safeValueBinder = (new StringValueBinder())
                ->setNullConversion(false)
                ->setBooleanConversion(false)
                ->setNumericConversion(false)
                ->setFormulaConversion(true);
        }

        return $this->safeValueBinder->bindValue($cell, $value);
    }
}

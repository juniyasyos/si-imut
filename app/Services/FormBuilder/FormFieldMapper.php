<?php

namespace App\Services\FormBuilder;

class FormFieldMapper
{
    const FIELD_TYPE_MAPPING = [
        'text' => 'text_input',
        'number' => 'numeric_input',
        'select' => 'select_dropdown',
        'radio' => 'radio_options',
        'checkbox' => 'checkbox_options',
        'date' => 'date_picker',
        'textarea' => 'textarea_input',
        'email' => 'email_input',
        'url' => 'url_input',
        'file' => 'file_upload',
    ];

    const FIELD_ICON_MAPPING = [
        // Field types untuk pelaporan mutu harian
        'short_text' => 'heroicon-o-pencil-square',
        'long_text' => 'heroicon-o-document-text',
        'number' => 'heroicon-o-calculator',
        'single_select' => 'heroicon-o-chevron-down',
        'boolean' => 'heroicon-o-check-circle',
        'date' => 'heroicon-o-calendar-days',
    ];

    public static function mapLegacyFieldType(string $legacyType): string
    {
        return self::FIELD_TYPE_MAPPING[$legacyType] ?? 'text_input';
    }

    public static function getFieldIcon(string $fieldType): string
    {
        return self::FIELD_ICON_MAPPING[$fieldType] ?? 'heroicon-o-pencil-square';
    }

    public static function getAllFieldTypes(): array
    {
        return [
            // Field types untuk pelaporan mutu harian
            'short_text' => '📝 Teks Pendek (Nama, Judul)',
            'long_text' => '📄 Teks Panjang (Catatan, Keterangan)',
            'number' => '🔢 Angka (Jumlah, Persentase)',
            'single_select' => '📋 Pilihan Tunggal (Dropdown)',
            'boolean' => '✅ Ya/Tidak (Status Validasi)',
            'date' => '📅 Tanggal (Tanggal Laporan)',
        ];
    }
    public static function getFieldTypeValidation(string $fieldType): array
    {
        return match ($fieldType) {
            'number' => ['numeric' => true, 'min' => null, 'max' => null],
            'date' => ['date' => true, 'date_format' => null],
            'long_text' => ['min_length' => null, 'max_length' => null],
            'short_text' => ['min_length' => null, 'max_length' => 255],
            'single_select' => ['options' => []],
            'boolean' => ['boolean' => true],
            default => ['required' => false],
        };
    }

    public static function requiresOptions(string $fieldType): bool
    {
        return in_array($fieldType, [
            'single_select',
            'boolean',
        ]);
    }
}

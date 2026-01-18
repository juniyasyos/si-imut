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
        // Enhanced form field types (sesuai enum di database)
        'text' => 'heroicon-o-pencil-square',
        'number' => 'heroicon-o-calculator',
        'date' => 'heroicon-o-calendar-days',
        'boolean' => 'heroicon-o-check-circle',
        'single_select' => 'heroicon-o-chevron-down',
        'multi_select' => 'heroicon-o-queue-list',
        'time_range' => 'heroicon-o-calendar',
        'time_duration' => 'heroicon-o-clock',

        // Legacy field types untuk compatibility
        'short_text' => 'heroicon-o-pencil-square',
        'long_text' => 'heroicon-o-document-text',
        'text_input' => 'heroicon-o-pencil-square',
        'numeric_input' => 'heroicon-o-calculator',
        'select_dropdown' => 'heroicon-o-chevron-down',
        'radio_options' => 'heroicon-o-radio',
        'checkbox_options' => 'heroicon-o-check-badge',
        'date_picker' => 'heroicon-o-calendar-days',
        'textarea_input' => 'heroicon-o-document-text',
        'email_input' => 'heroicon-o-envelope',
        'url_input' => 'heroicon-o-link',
        'file_upload' => 'heroicon-o-document-arrow-up',
    ];

    const TIME_FORMAT_OPTIONS = [
        'HM' => 'Jam:Menit (HH:MM)',
        'HMS' => 'Jam:Menit:Detik (HH:MM:SS)',
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
            // Enhanced form field types (sesuai database enum)
            'text' => '📝 Teks (Input sederhana)',
            'number' => '🔢 Angka (Numerik)',
            'date' => '📅 Tanggal',
            'boolean' => '✅ Ya/Tidak (Boolean)',
            'single_select' => '📋 Pilihan Tunggal (Toggle Buttons)',
            'multi_select' => '📄 Pilihan Multi (Checkbox)',
            'time_range' => '⏰ Rentang Waktu',
            'time_duration' => '⏱️ Durasi Waktu (dengan kalkulasi otomatis)',
        ];
    }

    public static function getTimeFormatOptions(): array
    {
        return self::TIME_FORMAT_OPTIONS;
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
            'multi_select',
            'boolean',
        ]);
    }

    public static function isCompositeFieldType(string $fieldType): bool
    {
        return in_array($fieldType, [
            'time_duration',
            'time_range',
        ]);
    }

    public static function getCompositeFieldStructure(string $fieldType): array
    {
        return match ($fieldType) {
            'time_duration' => [
                'start_time' => [
                    'label' => 'Waktu Mulai',
                    'type' => 'time',
                    'required' => true,
                ],
                'end_time' => [
                    'label' => 'Waktu Selesai',
                    'type' => 'time',
                    'required' => true,
                ],
                'valid_indicator' => [
                    'label' => 'Status Validasi',
                    'type' => 'indicator',
                    'required' => false,
                    'readonly' => true,
                ],
                'valid_duration_setting' => [
                    'label' => 'Threshold Durasi Valid (menit)',
                    'type' => 'number',
                    'required' => false,
                    'default' => 480,
                ],
            ],
            'time_range' => [
                'start_time' => [
                    'label' => 'Waktu Mulai',
                    'type' => 'time',
                    'required' => true,
                ],
                'end_time' => [
                    'label' => 'Waktu Selesai',
                    'type' => 'time',
                    'required' => true,
                ],
                'duration' => [
                    'label' => 'Durasi',
                    'type' => 'text',
                    'required' => false,
                    'readonly' => true,
                ],
                'valid_duration' => [
                    'label' => 'Durasi yang valid',
                    'type' => 'text',
                    'required' => false,
                    'readonly' => true,
                ],
            ],
            default => [],
        };
    }
}

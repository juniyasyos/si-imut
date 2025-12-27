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
        'rating_scale' => 'heroicon-o-star',
        'time_duration' => 'heroicon-o-clock',
        'time_range' => 'heroicon-o-calendar',
        'datetime' => 'heroicon-o-calendar-days',
        'conditional_trigger' => 'heroicon-o-adjustments-horizontal',
        'compliance_checker' => 'heroicon-o-shield-check',
        'weighted_score' => 'heroicon-o-scale',

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
            'rating_scale' => '⭐ Skala Rating (1-5)',
            'time_duration' => '⏱️ Durasi Waktu',
            'time_range' => '⏰ Rentang Waktu',
            'datetime' => '📅 Tanggal & Waktu',
            'conditional_trigger' => '🔀 Trigger Kondisional',
            'compliance_checker' => '🛡️ Checker Compliance',
            'weighted_score' => '⚖️ Skor Berbobot',
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
            'multi_select',
            'boolean',
            'rating_scale',
            'compliance_checker',
        ]);
    }
}

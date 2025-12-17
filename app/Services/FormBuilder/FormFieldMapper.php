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
        // Enhanced field types
        'short_text' => 'heroicon-o-pencil-square',
        'long_text' => 'heroicon-o-document-text',
        'number' => 'heroicon-o-calculator',
        'email' => 'heroicon-o-envelope',
        'url' => 'heroicon-o-link',
        'single_select' => 'heroicon-o-chevron-down',
        'multi_select' => 'heroicon-o-queue-list',
        'boolean' => 'heroicon-o-check-circle',
        'date' => 'heroicon-o-calendar-days',
        'datetime' => 'heroicon-o-clock',
        'time' => 'heroicon-o-clock',
        'file_upload' => 'heroicon-o-document-arrow-up',
        'image_upload' => 'heroicon-o-photo',

        // Legacy field types
        'text_input' => 'heroicon-o-pencil-square',
        'numeric_input' => 'heroicon-o-calculator',
        'email_input' => 'heroicon-o-envelope',
        'url_input' => 'heroicon-o-link',
        'select_dropdown' => 'heroicon-o-chevron-down',
        'radio_options' => 'heroicon-o-radio',
        'checkbox_options' => 'heroicon-o-check-circle',
        'textarea_input' => 'heroicon-o-document-text',
        'date_picker' => 'heroicon-o-calendar-days',
        'datetime_picker' => 'heroicon-o-clock',
        'time_picker' => 'heroicon-o-clock',
        'password_input' => 'heroicon-o-key',
        'phone_input' => 'heroicon-o-phone',
        'rich_editor' => 'heroicon-o-document-plus',
        'markdown_editor' => 'heroicon-o-code-bracket',
        'json_editor' => 'heroicon-o-code-bracket-square',
        'color_picker' => 'heroicon-o-paint-brush',
        'range_slider' => 'heroicon-o-adjustments-horizontal',
        'tags_input' => 'heroicon-o-hashtag',
        'toggle_switch' => 'heroicon-o-ellipsis-horizontal-circle',
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
            'short_text' => 'Teks Pendek',
            'long_text' => 'Teks Panjang',
            'number' => 'Input Angka',
            'email' => 'Input Email',
            'url' => 'Input URL',
            'single_select' => 'Pilihan Tunggal',
            'multi_select' => 'Pilihan Ganda',
            'boolean' => 'Ya/Tidak',
            'date' => 'Pilih Tanggal',
            'datetime' => 'Pilih Tanggal & Waktu',
            'time' => 'Pilih Waktu',
            'file_upload' => 'Upload File',
            'image_upload' => 'Upload Gambar',

            // Legacy support
            'text_input' => 'Input Teks',
            'numeric_input' => 'Input Angka',
            'email_input' => 'Input Email',
            'url_input' => 'Input URL',
            'select_dropdown' => 'Dropdown Pilihan',
            'radio_options' => 'Radio Button',
            'checkbox_options' => 'Checkbox',
            'textarea_input' => 'Area Teks',
            'date_picker' => 'Pilih Tanggal',
            'datetime_picker' => 'Pilih Tanggal & Waktu',
            'time_picker' => 'Pilih Waktu',
            'password_input' => 'Input Password',
            'phone_input' => 'Input Telepon',
            'rich_editor' => 'Rich Text Editor',
            'markdown_editor' => 'Markdown Editor',
            'json_editor' => 'JSON Editor',
            'color_picker' => 'Pilih Warna',
            'range_slider' => 'Range Slider',
            'tags_input' => 'Input Tags',
            'toggle_switch' => 'Toggle Switch',
        ];
    }

    public static function getFieldTypeValidation(string $fieldType): array
    {
        return match ($fieldType) {
            'numeric_input' => ['numeric' => true, 'min' => null, 'max' => null],
            'email_input' => ['email' => true],
            'url_input' => ['url' => true],
            'date_picker', 'datetime_picker' => ['date' => true, 'date_format' => null],
            'phone_input' => ['regex' => '/^[0-9+\-\s]+$/'],
            'file_upload', 'image_upload' => ['mimes' => null, 'max_size_mb' => null],
            'textarea_input', 'rich_editor', 'markdown_editor' => ['min_length' => null, 'max_length' => null],
            default => ['required' => false, 'min_length' => null, 'max_length' => null],
        };
    }

    public static function requiresOptions(string $fieldType): bool
    {
        return in_array($fieldType, [
            'single_select',
            'multi_select',
            'boolean',
            'select_dropdown',
            'radio_options',
            'checkbox_options',
        ]);
    }
}

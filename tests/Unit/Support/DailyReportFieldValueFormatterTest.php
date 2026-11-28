<?php

namespace Tests\Unit\Support;

use App\Modules\FormEngine\Models\EnhancedFormField;
use App\Modules\FormEngine\Models\FormFieldOption;
use App\Support\DailyReportFieldValueFormatter;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Tests\TestCase;

class DailyReportFieldValueFormatterTest extends TestCase
{
    private function makeField(string $type, array $options = [], array $validationConfig = []): EnhancedFormField
    {
        $field = new EnhancedFormField([
            'field_key' => 'test_field',
            'field_label' => 'Test Field',
            'field_type' => $type,
            'validation_config' => $validationConfig,
        ]);

        $optionModels = new EloquentCollection(
            array_map(
                fn (array $opt) => new FormFieldOption($opt),
                $options
            )
        );
        $field->setRelation('options', $optionModels);

        return $field;
    }

    public function test_text_and_generic_types_never_return_array_or_json_brackets(): void
    {
        foreach (['text', 'textarea', 'short_text', 'long_text', 'email', 'url'] as $type) {
            $field = $this->makeField($type);

            $this->assertSame('Catatan pasien', DailyReportFieldValueFormatter::formatForExport($field, 'Catatan pasien'));
            $this->assertSame('Catatan pasien', DailyReportFieldValueFormatter::formatForExport($field, ['Catatan pasien']));
            $this->assertSame('', DailyReportFieldValueFormatter::formatForExport($field, []));
            $this->assertSame('', DailyReportFieldValueFormatter::formatForExport($field, null));
            $this->assertSame('', DailyReportFieldValueFormatter::formatForExport($field, ''));
        }
    }

    public function test_number_and_rating_scale_unwrap_arrays_and_preserve_numeric_values(): void
    {
        foreach (['number', 'rating_scale'] as $type) {
            $field = $this->makeField($type);

            $this->assertSame(42, DailyReportFieldValueFormatter::formatForExport($field, 42));
            $this->assertSame(42, DailyReportFieldValueFormatter::formatForExport($field, ['42']));
            $this->assertSame(12.5, DailyReportFieldValueFormatter::formatForExport($field, ['12.5']));
            $this->assertSame('', DailyReportFieldValueFormatter::formatForExport($field, []));
            $this->assertSame('', DailyReportFieldValueFormatter::formatForExport($field, null));
        }
    }

    public function test_date_and_datetime_unwrap_arrays_and_format_safely(): void
    {
        $dateField = $this->makeField('date');
        $this->assertSame('15/07/2026', DailyReportFieldValueFormatter::formatForExport($dateField, '2026-07-15'));
        $this->assertSame('15/07/2026', DailyReportFieldValueFormatter::formatForExport($dateField, ['2026-07-15']));
        $this->assertSame('', DailyReportFieldValueFormatter::formatForExport($dateField, []));
        $this->assertSame('bukan-tanggal', DailyReportFieldValueFormatter::formatForExport($dateField, ['bukan-tanggal']));

        $dtField = $this->makeField('datetime');
        $this->assertSame('15/07/2026 09:30', DailyReportFieldValueFormatter::formatForExport($dtField, ['2026-07-15 09:30:00']));
    }

    public function test_boolean_handles_scalar_wrapped_array_empty_array_and_custom_options(): void
    {
        $plainBool = $this->makeField('boolean');
        $this->assertSame('Ya', DailyReportFieldValueFormatter::formatForExport($plainBool, '1'));
        $this->assertSame('Ya', DailyReportFieldValueFormatter::formatForExport($plainBool, ['1']));
        $this->assertSame('Ya', DailyReportFieldValueFormatter::formatForExport($plainBool, [true]));
        $this->assertSame('Tidak', DailyReportFieldValueFormatter::formatForExport($plainBool, '0'));
        $this->assertSame('Tidak', DailyReportFieldValueFormatter::formatForExport($plainBool, ['0']));
        $this->assertSame('', DailyReportFieldValueFormatter::formatForExport($plainBool, []));

        $customBool = $this->makeField('boolean', [
            ['option_value' => 'patuh', 'option_text' => 'Patuh'],
            ['option_value' => 'tidak_patuh', 'option_text' => 'Tidak Patuh'],
        ]);
        $this->assertSame('Patuh', DailyReportFieldValueFormatter::formatForExport($customBool, ['patuh']));
        $this->assertSame('Tidak Patuh', DailyReportFieldValueFormatter::formatForExport($customBool, 'tidak_patuh'));
    }

    public function test_single_select_and_multi_select_variants_resolve_labels_and_fallback(): void
    {
        $options = [
            ['option_value' => 'hand_rub', 'option_text' => 'Hand Rub'],
            ['option_value' => 'air_sabun', 'option_text' => 'Air + Sabun'],
        ];

        foreach (['single_select', 'select', 'radio'] as $type) {
            $field = $this->makeField($type, $options);
            $this->assertSame('Hand Rub', DailyReportFieldValueFormatter::formatForExport($field, 'hand_rub'));
            $this->assertSame('Hand Rub', DailyReportFieldValueFormatter::formatForExport($field, ['hand_rub']));
            $this->assertSame('opsi_lama', DailyReportFieldValueFormatter::formatForExport($field, ['opsi_lama']));
            $this->assertSame('', DailyReportFieldValueFormatter::formatForExport($field, []));
        }

        foreach (['multi_select', 'checkbox', 'conditional_trigger', 'compliance_checker'] as $type) {
            $field = $this->makeField($type, $options);
            $this->assertSame(
                'Hand Rub, Air + Sabun, opsi_lama',
                DailyReportFieldValueFormatter::formatForExport($field, ['hand_rub', 'air_sabun', 'opsi_lama'])
            );
            $this->assertSame('', DailyReportFieldValueFormatter::formatForExport($field, []));
        }
    }

    public function test_time_duration_formats_range_duration_and_validity(): void
    {
        $field = $this->makeField('time_duration');

        $this->assertSame(
            '2026-07-01 08:00 - 2026-07-01 08:15 (15 menit, Sesuai)',
            DailyReportFieldValueFormatter::formatForExport($field, [
                'type' => 'time_duration',
                'start_time' => '2026-07-01 08:00',
                'end_time' => '2026-07-01 08:15',
                'valid_indicator' => '1',
            ])
        );

        $this->assertSame(
            '08:00 -',
            DailyReportFieldValueFormatter::formatForExport($field, [
                'type' => 'time_duration',
                'start_time' => '08:00',
                'end_time' => null,
            ])
        );

        $this->assertSame(
            '20 menit',
            DailyReportFieldValueFormatter::formatForExport($field, ['duration' => 20])
        );

        $this->assertSame('', DailyReportFieldValueFormatter::formatForExport($field, ['type' => 'time_duration']));
    }

    public function test_time_range_shows_input_value_and_handles_null_stored_bounds(): void
    {
        $field = $this->makeField('time_range', [], [
            'default_start_time' => '08:00',
            'default_end_time' => '17:00',
        ]);

        // Old Create bug shape: start_time/end_time stored as null
        $this->assertSame(
            '09:30 (Sesuai, rentang 08:00 - 17:00)',
            DailyReportFieldValueFormatter::formatForExport($field, [
                'type' => 'time_range',
                'input_value' => '09:30',
                'start_time' => null,
                'end_time' => null,
                'valid_indicator' => '0',
            ])
        );

        // Edit / fixed Create shape: out of range
        $this->assertSame(
            '19:00 (Tidak Sesuai, rentang 08:00 - 17:00)',
            DailyReportFieldValueFormatter::formatForExport($field, [
                'input_value' => '19:00',
                'start_time' => '08:00',
                'end_time' => '17:00',
                'valid_indicator' => '0',
            ])
        );

        $this->assertSame('', DailyReportFieldValueFormatter::formatForExport($field, [
            'type' => 'time_range',
            'input_value' => null,
            'start_time' => '08:00',
            'end_time' => '17:00',
        ]));
    }

    public function test_validation_status_and_sheet_title_sanitization(): void
    {
        $this->assertSame('Valid', DailyReportFieldValueFormatter::formatValidationStatus('valid'));
        $this->assertSame('Tidak Valid', DailyReportFieldValueFormatter::formatValidationStatus('invalid'));
        $this->assertSame('Belum Divalidasi', DailyReportFieldValueFormatter::formatValidationStatus('pending'));
        $this->assertSame('Belum Divalidasi', DailyReportFieldValueFormatter::formatValidationStatus(null));

        $title = DailyReportFieldValueFormatter::sanitizeSheetTitle('Kepatuhan Cuci Tangan / APD [Unit: IGD]?*');
        $this->assertLessThanOrEqual(31, mb_strlen($title));
        $this->assertMatchesRegularExpression('/^[^\\\\\/\?\*\[\]:]+$/', $title);
        $this->assertSame('Monitoring', DailyReportFieldValueFormatter::sanitizeSheetTitle(''));
    }
}

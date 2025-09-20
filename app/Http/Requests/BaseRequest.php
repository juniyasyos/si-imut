<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Base Form Request with common validation patterns
 */
abstract class BaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization should be handled by policies
    }

    /**
     * Get common validation messages
     */
    public function messages(): array
    {
        return [
            'required' => 'Field :attribute wajib diisi.',
            'string' => 'Field :attribute harus berupa teks.',
            'email' => 'Field :attribute harus berupa email yang valid.',
            'unique' => 'Field :attribute sudah digunakan.',
            'exists' => 'Field :attribute tidak valid.',
            'min' => 'Field :attribute minimal :min karakter.',
            'max' => 'Field :attribute maksimal :max karakter.',
            'numeric' => 'Field :attribute harus berupa angka.',
            'integer' => 'Field :attribute harus berupa bilangan bulat.',
            'date' => 'Field :attribute harus berupa tanggal yang valid.',
            'date_format' => 'Field :attribute harus menggunakan format :format.',
            'after' => 'Field :attribute harus setelah :date.',
            'before' => 'Field :attribute harus sebelum :date.',
            'in' => 'Field :attribute yang dipilih tidak valid.',
            'between' => 'Field :attribute harus antara :min dan :max.',
            'url' => 'Field :attribute harus berupa URL yang valid.',
            'confirmed' => 'Konfirmasi :attribute tidak sesuai.',
            'regex' => 'Format :attribute tidak valid.',
        ];
    }

    /**
     * Get custom attribute names
     */
    public function attributes(): array
    {
        return [
            'name' => 'nama',
            'email' => 'email',
            'password' => 'kata sandi',
            'password_confirmation' => 'konfirmasi kata sandi',
            'status' => 'status',
            'assessment_period_start' => 'periode penilaian mulai',
            'assessment_period_end' => 'periode penilaian selesai',
            'created_by' => 'dibuat oleh',
            'unit_kerja_id' => 'unit kerja',
            'imut_data_id' => 'data IMUT',
            'imut_profil_id' => 'profil IMUT',
            'laporan_unit_kerja_id' => 'laporan unit kerja',
            'numerator_value' => 'nilai pembilang',
            'denominator_value' => 'nilai penyebut',
            'target_value' => 'nilai target',
            'target_operator' => 'operator target',
            'version' => 'versi',
            'rationale' => 'rasional',
            'quality_dimension' => 'dimensi kualitas',
            'objective' => 'tujuan',
            'operational_definition' => 'definisi operasional',
            'indicator_type' => 'tipe indikator',
            'analysis' => 'analisis',
            'recommendations' => 'rekomendasi',
        ];
    }

    /**
     * Common validation rules for ID fields
     */
    protected function idRules(string $table, bool $required = true): array
    {
        $rules = ['integer', 'min:1', "exists:{$table},id"];

        if ($required) {
            array_unshift($rules, 'required');
        } else {
            array_unshift($rules, 'nullable');
        }

        return $rules;
    }

    /**
     * Common validation rules for text fields
     */
    protected function textRules(int $min = 3, int $max = 255, bool $required = true): array
    {
        $rules = ['string', "min:{$min}", "max:{$max}"];

        if ($required) {
            array_unshift($rules, 'required');
        } else {
            array_unshift($rules, 'nullable');
        }

        return $rules;
    }

    /**
     * Common validation rules for numeric fields
     */
    protected function numericRules(float $min = 0, ?float $max = null, bool $required = true): array
    {
        $rules = ['numeric', "min:{$min}"];

        if ($max !== null) {
            $rules[] = "max:{$max}";
        }

        if ($required) {
            array_unshift($rules, 'required');
        } else {
            array_unshift($rules, 'nullable');
        }

        return $rules;
    }

    /**
     * Common validation rules for date fields
     */
    protected function dateRules(bool $required = true): array
    {
        $rules = ['date'];

        if ($required) {
            array_unshift($rules, 'required');
        } else {
            array_unshift($rules, 'nullable');
        }

        return $rules;
    }
}

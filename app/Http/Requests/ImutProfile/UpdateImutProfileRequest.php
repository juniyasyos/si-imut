<?php

namespace App\Http\Requests\ImutProfile;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class UpdateImutProfileRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'version' => [
                'required',
                'string',
                'min:1',
                'max:50'
            ],
            'rationale' => $this->textRules(10, 2000),
            'quality_dimension' => $this->textRules(3, 255),
            'objective' => $this->textRules(10, 1000),
            'operational_definition' => $this->textRules(10, 2000),
            'indicator_type' => [
                'required',
                'string',
                Rule::in(['structure', 'process', 'outcome', 'output', 'input'])
            ],
            'numerator_formula' => $this->textRules(5, 500),
            'denominator_formula' => $this->textRules(5, 500),
            'inclusion_criteria' => $this->textRules(10, 1000, false),
            'exclusion_criteria' => $this->textRules(10, 1000, false),
            'data_source' => $this->textRules(3, 255),
            'data_collection_frequency' => [
                'required',
                'string',
                Rule::in(['daily', 'weekly', 'monthly', 'quarterly', 'yearly'])
            ],
            'analysis_plan' => $this->textRules(10, 1000, false),
            'target_operator' => [
                'required',
                'string',
                Rule::in(['>=', '<=', '=', '>', '<'])
            ],
            'target_value' => $this->numericRules(0, 999999),
            'analysis_period_type' => [
                'required',
                'string',
                Rule::in(['month', 'quarter', 'year'])
            ],
            'analysis_period_value' => [
                'required',
                'integer',
                'min:1',
                'max:12'
            ],
            'start_period' => $this->dateRules(),
            'end_period' => [
                'required',
                'date',
                'after:start_period'
            ],
            'data_collection_method' => $this->textRules(3, 255, false),
            'sampling_method' => $this->textRules(3, 255, false),
            'data_collection_tool' => $this->textRules(3, 255, false),
            'responsible_person' => $this->textRules(3, 255, false),
        ];
    }

    /**
     * Custom validation messages
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'end_period.after' => 'Periode akhir harus setelah periode awal.',
            'indicator_type.in' => 'Tipe indikator harus salah satu dari: structure, process, outcome, output, input.',
            'data_collection_frequency.in' => 'Frekuensi pengumpulan data tidak valid.',
            'target_operator.in' => 'Operator target tidak valid.',
            'analysis_period_type.in' => 'Tipe periode analisis tidak valid.',
        ]);
    }

    /**
     * Get custom attribute names
     */
    public function attributes(): array
    {
        return array_merge(parent::attributes(), [
            'numerator_formula' => 'formula pembilang',
            'denominator_formula' => 'formula penyebut',
            'inclusion_criteria' => 'kriteria inklusi',
            'exclusion_criteria' => 'kriteria eksklusi',
            'data_source' => 'sumber data',
            'data_collection_frequency' => 'frekuensi pengumpulan data',
            'analysis_plan' => 'rencana analisis',
            'analysis_period_type' => 'tipe periode analisis',
            'analysis_period_value' => 'nilai periode analisis',
            'start_period' => 'periode awal',
            'end_period' => 'periode akhir',
            'data_collection_method' => 'metode pengumpulan data',
            'sampling_method' => 'metode sampling',
            'data_collection_tool' => 'alat pengumpulan data',
            'responsible_person' => 'penanggung jawab',
        ]);
    }
}

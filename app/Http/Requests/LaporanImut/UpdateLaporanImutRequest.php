<?php

namespace App\Http\Requests\LaporanImut;

use App\Http\Requests\BaseRequest;
use App\Models\LaporanImut;
use Illuminate\Validation\Rule;

class UpdateLaporanImutRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $laporanId = route('laporan');

        return [
            'name' => [
                'required',
                'string',
                'min:3',
                'max:255',
                Rule::unique('laporan_imut', 'name')->ignore($laporanId)
            ],
            'status' => [
                'required',
                'string',
                Rule::in([
                    LaporanImut::STATUS_PROCESS,
                    LaporanImut::STATUS_COMPLETE,
                    LaporanImut::STATUS_COMINGSOON,
                ])
            ],
            'assessment_period_start' => [
                'required',
                'date',
                'before:assessment_period_end'
            ],
            'assessment_period_end' => [
                'required',
                'date',
                'after:assessment_period_start'
            ],
            'unit_kerja_ids' => [
                'nullable',
                'array'
            ],
            'unit_kerja_ids.*' => $this->idRules('unit_kerja', false),
            'description' => [
                'nullable',
                'string',
                'max:1000'
            ],
        ];
    }

    /**
     * Custom validation messages
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'name.unique' => 'Nama laporan sudah digunakan.',
            'assessment_period_start.before' => 'Periode mulai harus sebelum periode selesai.',
            'assessment_period_end.after' => 'Periode selesai harus setelah periode mulai.',
            'unit_kerja_ids.array' => 'Unit kerja harus berupa array.',
            'unit_kerja_ids.*.exists' => 'Unit kerja yang dipilih tidak valid.',
        ]);
    }
}

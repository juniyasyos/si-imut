<?php

namespace App\Http\Requests\ImutPenilaian;

use App\Http\Requests\BaseRequest;

class StoreImutPenilaianRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'imut_profil_id' => $this->idRules('imut_profil'),
            'laporan_unit_kerja_id' => $this->idRules('laporan_unit_kerja'),
            'numerator_value' => $this->numericRules(0, 999999999, false),
            'denominator_value' => $this->numericRules(0.01, 999999999, false),
            'analysis' => $this->textRules(10, 2000, false),
            'recommendations' => $this->textRules(10, 2000, false),
        ];
    }

    /**
     * Custom validation messages
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'denominator_value.min' => 'Nilai penyebut harus lebih dari 0.',
        ]);
    }
}

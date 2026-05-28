<?php

namespace App\Filament\Resources\LaporanImutResource\Pages\Helpers\Forms;

use Filament\Forms;
use Illuminate\Support\HtmlString;

class RunAutoGenerationNowFormHelper
{
    public static function schema(): array
    {
        return [
            Forms\Components\Placeholder::make('manual_run_info')
                ->label('Informasi')
                ->content(new HtmlString(
                    '<div class="text-sm space-y-1">'
                    . '<p>Action ini hanya untuk <strong>bulan berjalan</strong>, bukan bulan sebelumnya.</p>'
                    . '<p>Jika laporan bulan ini sudah ada, sistem <strong>tidak akan membuat ulang</strong> untuk mencegah overwrite data.</p>'
                    . '</div>'
                )),

            Forms\Components\Radio::make('manual_run_choice')
                ->label('Pilih aksi')
                ->options([
                    'create_current_month' => 'Buat laporan untuk bulan ini',
                    'cancel' => 'Batalkan',
                ])
                ->default('create_current_month')
                ->required()
                ->inline(false),
        ];
    }
}

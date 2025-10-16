<?php

namespace App\Domains\Reporting\Events;

use App\Domains\Reporting\Models\LaporanImut;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LaporanGenerated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly LaporanImut $laporan)
    {
    }
}

<?php

namespace App\Domains\Imut\Events;

use App\Domains\Imut\Models\ImutPenilaian;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ImutPenilaianSubmitted
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly ImutPenilaian $penilaian)
    {
    }
}

<?php

namespace App\Kernel\Traits;

use App\Models\User;
use App\Models\UnitKerja;
use Carbon\Carbon;
use Faker\Factory as Faker;

trait ImutInitializer
{
    protected $faker;
    protected $now;
    protected $adminUserId;
    protected $unitKerjaIds;
    protected int $totalYears = 1;

    protected function initImut(): void
    {
        $this->faker       = Faker::create();
        $this->now         = Carbon::now();
        $this->adminUserId = User::where('name', 'admin')->value('id') ?? 1;
        $this->unitKerjaIds = UnitKerja::pluck('id')->toArray();
    }
}

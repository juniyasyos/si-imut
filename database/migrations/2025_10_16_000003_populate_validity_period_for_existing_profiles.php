<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Set valid_from untuk profil existing berdasarkan created_at
        // Ini safe untuk production karena hanya update data existing
        DB::table('imut_profil')
            ->whereNull('valid_from')
            ->update([
                'valid_from' => DB::raw('DATE(created_at)'),
                'updated_at' => Carbon::now()
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback: set valid_from ke null lagi
        DB::table('imut_profil')->update(['valid_from' => null]);
    }
};

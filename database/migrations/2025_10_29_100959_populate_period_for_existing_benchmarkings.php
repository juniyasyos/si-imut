<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Populate period_start dan period_end untuk existing benchmarking data
     * berdasarkan year dan month yang sudah ada.
     */
    public function up(): void
    {
        // Get all existing benchmarking records
        $benchmarkings = DB::table('imut_benchmarkings')
            ->whereNull('period_start')
            ->get();

        foreach ($benchmarkings as $benchmark) {
            try {
                // Calculate period_start = first day of the month
                $periodStart = Carbon::createFromDate($benchmark->year, $benchmark->month, 1)
                    ->startOfMonth()
                    ->format('Y-m-d');

                // Calculate period_end = last day of the month
                $periodEnd = Carbon::createFromDate($benchmark->year, $benchmark->month, 1)
                    ->endOfMonth()
                    ->format('Y-m-d');

                // Update the record
                DB::table('imut_benchmarkings')
                    ->where('id', $benchmark->id)
                    ->update([
                        'period_start' => $periodStart,
                        'period_end' => $periodEnd,
                        'is_active' => true,
                        'updated_at' => now(),
                    ]);

            } catch (\Exception $e) {
                // Log error tapi tetap lanjut untuk record lainnya
                Log::warning("Failed to populate period for benchmarking ID {$benchmark->id}: " . $e->getMessage());
            }
        }

        Log::info("Populated period data for " . $benchmarkings->count() . " benchmarking records");
    }

    /**
     * Reverse the migrations.
     *
     * Clear period_start dan period_end (rollback to null)
     */
    public function down(): void
    {
        DB::table('imut_benchmarkings')->update([
            'period_start' => null,
            'period_end' => null,
            'is_active' => true,
        ]);
    }
};

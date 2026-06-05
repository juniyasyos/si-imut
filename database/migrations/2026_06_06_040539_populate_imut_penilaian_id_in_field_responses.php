<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Populates imut_penilaian_id for existing FieldResponse records
     * by tracing: FieldResponse → DailyReportResponse → FormTemplate → ImutProfil → ImutPenilaian
     */
    public function up(): void
    {
        DB::statement(<<<SQL
            UPDATE field_responses fr
            INNER JOIN daily_report_responses drr ON fr.daily_report_response_id = drr.id
            INNER JOIN form_templates ft ON drr.form_template_id = ft.id
            INNER JOIN imut_penilaians ip_pn ON ft.imut_profile_id = ip_pn.imut_profil_id
            INNER JOIN laporan_unit_kerjas luk ON ip_pn.laporan_unit_kerja_id = luk.id
            INNER JOIN laporan_imuts li ON luk.laporan_imut_id = li.id
            SET fr.imut_penilaian_id = ip_pn.id
            WHERE 
                luk.unit_kerja_id = drr.unit_kerja_id
                AND drr.report_date >= li.assessment_period_start
                AND drr.report_date <= li.assessment_period_end
                AND fr.imut_penilaian_id IS NULL
        SQL);

        $updated = DB::table('field_responses')->whereNotNull('imut_penilaian_id')->count();
        echo "\n✓ Updated {$updated} FieldResponse records with imut_penilaian_id\n";

        $unpopulated = DB::table('field_responses')->whereNull('imut_penilaian_id')->count();
        if ($unpopulated > 0) {
            echo "⚠ Warning: {$unpopulated} FieldResponse records still have NULL imut_penilaian_id\n";
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('field_responses')->update(['imut_penilaian_id' => NULL]);
        echo "\n✓ Cleared imut_penilaian_id from all FieldResponse records\n";
    }
};

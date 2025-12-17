<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // First, convert existing enum values to boolean
        DB::statement("UPDATE daily_report_responses SET compliance_status = CASE 
            WHEN compliance_status = 'compliant' THEN '1' 
            ELSE '0' 
        END");

        // Change column type from enum to boolean
        Schema::table('daily_report_responses', function (Blueprint $table) {
            $table->boolean('compliance_status')->default(false)->change();
        });

        // Also update form_templates if needed
        if (Schema::hasColumn('form_templates', 'compliance_status')) {
            Schema::table('form_templates', function (Blueprint $table) {
                $table->boolean('compliance_status')->default(true)->change();
            });
        }
    }

    public function down(): void
    {
        // Convert boolean back to enum
        Schema::table('daily_report_responses', function (Blueprint $table) {
            $table->enum('compliance_status', ['compliant', 'non_compliant', 'pending'])->default('pending')->change();
        });

        // Convert boolean values back to enum strings
        DB::statement("UPDATE daily_report_responses SET compliance_status = CASE 
            WHEN compliance_status = '1' THEN 'compliant' 
            ELSE 'non_compliant' 
        END");

        if (Schema::hasColumn('form_templates', 'compliance_status')) {
            Schema::table('form_templates', function (Blueprint $table) {
                $table->enum('compliance_status', ['active', 'inactive'])->default('active')->change();
            });
        }
    }
};

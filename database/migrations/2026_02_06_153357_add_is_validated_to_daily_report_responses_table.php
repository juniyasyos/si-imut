<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('daily_report_responses', function (Blueprint $table) {
            $table->boolean('is_validated')->default(false)->after('calculation_details');
            $table->foreignId('validated_by')->nullable()->constrained('users')->onDelete('set null')->after('is_validated');
            $table->timestamp('validated_at')->nullable()->after('validated_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_report_responses', function (Blueprint $table) {
            $table->dropForeign(['validated_by']);
            $table->dropColumn(['is_validated', 'validated_by', 'validated_at']);
        });
    }
};

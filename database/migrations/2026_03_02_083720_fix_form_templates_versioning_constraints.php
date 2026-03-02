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
        Schema::table('form_templates', function (Blueprint $table) {
            // Drop the old unique constraint that prevents multiple templates per profile
            $table->dropUnique('unique_form_template_per_profile');
            
            // Add new unique constraint for profile_id + version combination
            $table->unique(['imut_profile_id', 'version'], 'unique_profile_version');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('form_templates', function (Blueprint $table) {
            // Restore the old constraint
            $table->dropUnique('unique_profile_version');
            $table->unique('imut_profile_id', 'unique_form_template_per_profile');
        });
    }
};

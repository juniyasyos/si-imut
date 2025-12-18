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
        // Enhanced Form Templates
        Schema::create('form_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('imut_data_id')->constrained('imut_data')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('compliance_method', ['auto_calculate', 'manual', 'weighted'])->default('auto_calculate');
            $table->boolean('auto_fail_on_critical')->default(true);
            $table->json('scoring_config')->nullable();
            $table->timestamps();
        });

        // Enhanced Form Fields
        Schema::create('enhanced_form_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_template_id')->constrained('form_templates')->onDelete('cascade');
            $table->string('field_key')->index();
            $table->string('field_label');
            $table->text('field_description')->nullable();
            $table->enum('field_type', [
                'text',
                'number',
                'date',
                'boolean',
                'single_select',
                'multi_select',
                'rating_scale',
                'time_duration',
                'time_range',
                'datetime',
                'conditional_trigger',
                'compliance_checker',
                'weighted_score'
            ]);
            $table->json('validation_config')->nullable();
            $table->integer('compliance_weight')->default(1);
            $table->boolean('is_critical_field')->default(false);
            $table->json('conditional_logic')->nullable();
            $table->integer('order_index')->default(0);
            $table->timestamps();

            $table->unique(['form_template_id', 'field_key']);
        });

        // Field Options
        Schema::create('form_field_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_field_id')->constrained('enhanced_form_fields')->onDelete('cascade');
            $table->string('option_key');
            $table->string('option_label');
            $table->integer('compliance_value')->default(0);
            $table->text('option_description')->nullable();
            $table->integer('order_index')->default(0);
            $table->timestamps();
        });

        // Update existing Daily Report Responses table
        if (Schema::hasTable('daily_report_responses')) {
            Schema::table('daily_report_responses', function (Blueprint $table) {
                if (!Schema::hasColumn('daily_report_responses', 'form_template_id')) {
                    $table->foreignId('form_template_id')->nullable()->constrained('form_templates');
                }
                if (!Schema::hasColumn('daily_report_responses', 'total_score')) {
                    $table->decimal('total_score', 5, 2)->default(0);
                }
                if (!Schema::hasColumn('daily_report_responses', 'auto_calculated')) {
                    $table->boolean('auto_calculated')->default(true);
                }
                if (!Schema::hasColumn('daily_report_responses', 'calculation_details')) {
                    $table->json('calculation_details')->nullable();
                }
            });
        }

        // Field Responses
        Schema::create('field_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_report_response_id')->constrained('daily_report_responses')->onDelete('cascade');
            $table->foreignId('form_field_id')->constrained('enhanced_form_fields');
            $table->json('field_value'); // Store any type of value
            $table->decimal('compliance_score', 5, 2)->default(0);
            $table->boolean('is_valid')->default(true);
            $table->text('validation_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('field_responses');

        // Only drop columns we added, not the entire table
        if (Schema::hasTable('daily_report_responses')) {
            Schema::table('daily_report_responses', function (Blueprint $table) {
                if (Schema::hasColumn('daily_report_responses', 'calculation_details')) {
                    $table->dropColumn('calculation_details');
                }
                if (Schema::hasColumn('daily_report_responses', 'auto_calculated')) {
                    $table->dropColumn('auto_calculated');
                }
                if (Schema::hasColumn('daily_report_responses', 'total_score')) {
                    $table->dropColumn('total_score');
                }
                if (Schema::hasColumn('daily_report_responses', 'form_template_id')) {
                    $table->dropForeign(['form_template_id']);
                    $table->dropColumn('form_template_id');
                }
            });
        }

        Schema::dropIfExists('form_field_options');
        Schema::dropIfExists('enhanced_form_fields');
        Schema::dropIfExists('form_templates');
    }
};

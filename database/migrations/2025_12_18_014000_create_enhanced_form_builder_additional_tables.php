<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create form_templates table if not exists
        if (!Schema::hasTable('form_templates')) {
            Schema::create('form_templates', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('imut_data_id');
                $table->string('title');
                $table->text('description')->nullable();
                $table->enum('compliance_method', ['auto_calculate', 'manual_review', 'weighted_average'])
                    ->default('auto_calculate');
                $table->boolean('auto_fail_on_critical')->default(true);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->foreign('imut_data_id')->references('id')->on('imut_data')->onDelete('cascade');
                $table->index(['imut_data_id', 'is_active']);
            });
        }

        // Create enhanced_form_fields table if not exists
        if (!Schema::hasTable('enhanced_form_fields')) {
            Schema::create('enhanced_form_fields', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('form_template_id');
                $table->string('field_key');
                $table->string('field_name');
                $table->text('field_description')->nullable();
                $table->enum('field_type', [
                    'short_text',
                    'long_text',
                    'number',
                    'boolean',
                    'single_select',
                    'multi_select',
                    'rating_scale',
                    'time_duration',
                    'time_range',
                    'conditional_trigger',
                    'compliance_checker'
                ]);
                $table->json('validation_config')->nullable();
                $table->decimal('compliance_weight', 3, 2)->default(1.0);
                $table->boolean('is_critical_field')->default(false);
                $table->unsignedBigInteger('parent_field_id')->nullable();
                $table->string('condition_value')->nullable();
                $table->integer('order_index')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->foreign('form_template_id')->references('id')->on('form_templates')->onDelete('cascade');
                $table->foreign('parent_field_id')->references('id')->on('enhanced_form_fields')->onDelete('set null');

                $table->unique(['form_template_id', 'field_key'], 'unique_template_field_key');
                $table->index(['form_template_id', 'is_active', 'order_index'], 'idx_template_active_order');
                $table->index(['parent_field_id', 'condition_value'], 'idx_parent_condition');
            });
        }

        // Create form_field_options table if not exists
        if (!Schema::hasTable('form_field_options')) {
            Schema::create('form_field_options', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('enhanced_form_field_id');
                $table->string('option_text');
                $table->string('option_value');
                $table->tinyInteger('compliance_value')->default(0)->comment('0=fail, 1=pass, 2=excellent');
                $table->integer('order_index')->default(0);
                $table->timestamps();

                $table->foreign('enhanced_form_field_id')->references('id')->on('enhanced_form_fields')->onDelete('cascade');
                $table->index(['enhanced_form_field_id', 'order_index']);
            });
        }

        // Create field_responses table if not exists
        if (!Schema::hasTable('field_responses')) {
            Schema::create('field_responses', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('daily_report_response_id');
                $table->unsignedBigInteger('enhanced_form_field_id');
                $table->text('response_value');
                $table->decimal('field_score', 5, 2)->nullable();
                $table->json('calculation_details')->nullable();
                $table->timestamps();

                $table->foreign('daily_report_response_id')->references('id')->on('daily_report_responses')->onDelete('cascade');
                $table->foreign('enhanced_form_field_id')->references('id')->on('enhanced_form_fields')->onDelete('cascade');

                $table->unique(['daily_report_response_id', 'enhanced_form_field_id'], 'unique_response_field');
                $table->index(['enhanced_form_field_id']);
            });
        }

        // Add new columns to existing daily_report_responses table if they don't exist
        Schema::table('daily_report_responses', function (Blueprint $table) {
            if (!Schema::hasColumn('daily_report_responses', 'form_template_id')) {
                $table->unsignedBigInteger('form_template_id')->nullable()->after('id');
                $table->foreign('form_template_id')->references('id')->on('form_templates')->onDelete('set null');
            }

            if (!Schema::hasColumn('daily_report_responses', 'total_score')) {
                $table->decimal('total_score', 5, 2)->default(0)->after('report_date');
            }

            if (!Schema::hasColumn('daily_report_responses', 'compliance_status')) {
                $table->enum('compliance_status', ['compliant', 'non_compliant', 'pending'])->default('pending')->after('total_score');
            }

            if (!Schema::hasColumn('daily_report_responses', 'auto_calculated')) {
                $table->boolean('auto_calculated')->default(true)->after('compliance_status');
            }

            if (!Schema::hasColumn('daily_report_responses', 'calculation_details')) {
                $table->json('calculation_details')->nullable()->after('auto_calculated');
            }
        });
    }

    public function down(): void
    {
        // Remove new columns from daily_report_responses
        Schema::table('daily_report_responses', function (Blueprint $table) {
            if (Schema::hasColumn('daily_report_responses', 'calculation_details')) {
                $table->dropColumn('calculation_details');
            }
            if (Schema::hasColumn('daily_report_responses', 'auto_calculated')) {
                $table->dropColumn('auto_calculated');
            }
            if (Schema::hasColumn('daily_report_responses', 'compliance_status')) {
                $table->dropColumn('compliance_status');
            }
            if (Schema::hasColumn('daily_report_responses', 'total_score')) {
                $table->dropColumn('total_score');
            }
            if (Schema::hasColumn('daily_report_responses', 'form_template_id')) {
                $table->dropForeign(['form_template_id']);
                $table->dropColumn('form_template_id');
            }
        });

        Schema::dropIfExists('field_responses');
        Schema::dropIfExists('form_field_options');
        Schema::dropIfExists('enhanced_form_fields');
        Schema::dropIfExists('form_templates');
    }
};

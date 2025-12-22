<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_fields', function (Blueprint $table) {
            $table->id();
            $table->string('key');
            $table->string('label');
            $table->text('description')->nullable();
            $table->enum('type', ['text', 'textarea', 'number', 'date', 'bool', 'select', 'radio', 'checkbox']);
            $table->boolean('is_required')->default(false);
            $table->json('options')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->unique(['form_template_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_fields');
    }
};

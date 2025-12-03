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
            $table->foreignId('form_header_id')->constrained('form_headers')->onDelete('cascade');
            $table->string('key');
            $table->string('label');
            $table->enum('type', ['text', 'textarea', 'number', 'date', 'bool', 'select', 'radio', 'checkbox']);
            $table->boolean('is_required')->default(false);
            $table->json('options')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->unique(['form_header_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_fields');
    }
};

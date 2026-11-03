<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('galleries', function (Blueprint $table) {
            $table->id();

            // Modelo al que pertenece la galería
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');

            $table->string('name')->nullable();

            $table->timestamps();

            // Una galería por modelo
            $table->unique(
                ['model_type', 'model_id'],
                'galleries_model_type_model_id_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('galleries');
    }
};

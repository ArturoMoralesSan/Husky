<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('slug')->unique();

            $table->string('short_description')->nullable();
            $table->longText('description')->nullable();

            $table->string('material')->nullable();
            $table->string('treatment')->nullable();
            $table->decimal('retention', 10, 4)->nullable();
            $table->string('retention_unit')->nullable();

            $table->string('image')->nullable();
            $table->string('image_alt')->nullable();

            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();

            $table->string('name')->nullable();

            $table->decimal('length', 10, 3)->nullable();
            $table->string('length_unit')->nullable();

            $table->decimal('diameter', 10, 3)->nullable();
            $table->string('diameter_unit')->nullable();

            $table->decimal('width', 10, 3)->nullable();
            $table->string('width_unit')->nullable();

            $table->decimal('thickness', 10, 3)->nullable();
            $table->string('thickness_unit')->nullable();

            $table->json('specifications')->nullable();

            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['product_id', 'is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};

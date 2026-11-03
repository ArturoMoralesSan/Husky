<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();

            // Datos del solicitante
            $table->string('name');
            $table->string('company')->nullable();
            $table->string('email');
            $table->string('phone', 50);

            // Producto o servicio seleccionado
            $table->foreignId('product_id')
                ->nullable()
                ->constrained('products')
                ->nullOnDelete();

            $table->foreignId('service_id')
                ->nullable()
                ->constrained('services')
                ->nullOnDelete();

            // Nombre conservado aunque cambie el registro original
            $table->string('item_name');

            // Información adicional del proyecto
            $table->text('description')->nullable();

            // Seguimiento administrativo
            $table->string('status', 30)->default('pendiente');

            $table->timestamps();

            $table->index('status');
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};

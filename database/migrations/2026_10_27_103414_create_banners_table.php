
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();

            // Información del banner
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->text('description')->nullable();

            // Una imagen por banner
            $table->string('image');
            $table->string('alt_text')->nullable();

            // Enlace y botón opcionales
            $table->string('link_url')->nullable();
            $table->string('button_text')->nullable();
            $table->boolean('open_in_new_tab')->default(false);

            // Sección donde se mostrará
            $table->string('position')->default('home');

            // Orden de aparición dentro de la sección
            $table->unsignedInteger('sort_order')->default(0);

            // Estado de publicación
            $table->boolean('is_active')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();

            // Metadatos SEO opcionales
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();

            $table->timestamps();

            // Índice para consultar y ordenar los banners
            $table->index([
                'position',
                'is_active',
                'sort_order',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};

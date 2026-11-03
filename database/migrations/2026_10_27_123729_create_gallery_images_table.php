<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gallery_images', function (Blueprint $table) {
            $table->id();

            $table->foreignId('gallery_id')
                ->constrained('galleries')
                ->cascadeOnDelete();

            $table->string('file');
            $table->string('alt')->nullable();

            $table->boolean('is_primary')->nullable()->default(false);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(
                ['gallery_id', 'sort_order'],
                'gallery_images_gallery_sort_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gallery_images');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();

            $table->string('email')->nullable();

            $table->string('phone_primary')->nullable();
            $table->string('phone_secondary')->nullable();
            $table->string('phone_tertiary')->nullable();
            $table->string('whatsapp')->nullable();
            $table->text('address')->nullable();

            $table->string('schedule_weekdays')->nullable();
            $table->string('schedule_saturday')->nullable();

            $table->string('facebook_url')->nullable();
            $table->string('instagram_url')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
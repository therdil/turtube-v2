<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('site_name')->default('TurTube');
            $table->string('logo')->nullable();
            $table->string('banner')->nullable();
            $table->text('announcement')->nullable();
            $table->boolean('announcement_enabled')->default(false);
            $table->boolean('maintenance_mode')->default(false);
            $table->text('maintenance_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};

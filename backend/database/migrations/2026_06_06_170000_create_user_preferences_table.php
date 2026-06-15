<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('theme_color', 30)->default('blue');
            $table->string('sidebar_theme', 30)->default('dark');
            $table->string('header_theme', 30)->default('light');
            $table->string('dark_mode', 30)->default('light');
            $table->string('layout_mode', 30)->default('standard');
            $table->string('card_style', 30)->default('modern');
            $table->timestamps();

            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_preferences');
    }
};

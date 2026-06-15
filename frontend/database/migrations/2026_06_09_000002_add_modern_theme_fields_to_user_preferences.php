<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_preferences', function (Blueprint $table): void {
            $table->string('theme_preset', 30)->default('timber')->after('user_id');
            $table->string('border_radius', 30)->default('comfortable')->after('card_style');
            $table->string('font_family', 30)->default('inter')->after('border_radius');
        });
    }

    public function down(): void
    {
        Schema::table('user_preferences', function (Blueprint $table): void {
            $table->dropColumn(['theme_preset', 'border_radius', 'font_family']);
        });
    }
};

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPreference extends Model
{
    protected $fillable = [
        'user_id',
        'theme_preset',
        'theme_color',
        'sidebar_theme',
        'header_theme',
        'dark_mode',
        'layout_mode',
        'card_style',
        'border_radius',
        'font_family',
    ];
}

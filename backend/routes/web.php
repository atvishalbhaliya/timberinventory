<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response()->json([
    'success' => true,
    'message' => 'Timber Inventory backend API is running.',
    'data' => [
        'application' => 'backend',
        'type' => 'api-only',
        'health' => '/api/v1/health',
    ],
]));

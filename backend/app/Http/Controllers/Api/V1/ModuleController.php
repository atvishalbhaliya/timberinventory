<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ModuleController extends Controller
{
    public function index()
    {
        $modules = DB::table('erp_modules')
            ->select('module_name')
            ->where('status', 'Active')
            ->whereNull('deleted_at')
            ->orderBy('display_order')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $modules
        ]);
    }
}
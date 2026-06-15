<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuditLogService
{
    public function record(Request $request, string $table, string $action, ?int $recordId, mixed $oldValue = null, mixed $newValue = null): void
    {
        $user = $request->user();
        $branchId = $user?->branch_id ?: DB::table('branch_master')
            ->where('tenant_id', $user?->tenant_id)
            ->orderBy('branch_id')
            ->value('branch_id');

        DB::table('audit_log')->insert([
            'tenant_id' => $user?->tenant_id,
            'branch_id' => $branchId,
            'table_name' => $table,
            'action_type' => $action,
            'record_id' => $recordId,
            'old_value' => $oldValue ? json_encode($oldValue) : null,
            'new_value' => $newValue ? json_encode($newValue) : null,
            'user_id' => $user?->id,
            'ip_address' => $request->ip(),
            'action_time' => now(),
            'created_by' => $user?->id,
            'updated_by' => $user?->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

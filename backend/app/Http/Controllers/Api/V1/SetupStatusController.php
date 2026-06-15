<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class SetupStatusController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $checks = [
            'database_connected' => $this->databaseConnected(),
            'redis_connected' => $this->redisConnected(),
            'storage_linked' => is_link(public_path('storage')) || file_exists(public_path('storage')),
            'queue_running' => $this->queueIsConfigured(),
            'tenant_exists' => $this->tableHasRows('tenant_master'),
            'admin_user_exists' => $this->adminUserExists(),
        ];

        $warnings = collect($checks)
            ->filter(fn (bool $passed): bool => ! $passed)
            ->keys()
            ->values()
            ->all();

        return response()->json([
            'success' => count($warnings) === 0,
            'message' => count($warnings) === 0 ? 'Application setup is ready.' : 'Application setup requires attention.',
            'data' => [
                'checks' => $checks,
                'warnings' => $warnings,
            ],
        ]);
    }

    private function databaseConnected(): bool
    {
        try {
            DB::connection()->getPdo();

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    private function redisConnected(): bool
    {
        if (! $this->redisRequired()) {
            return true;
        }

        try {
            Redis::connection()->ping();

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    private function queueIsConfigured(): bool
    {
        if (config('queue.default') === 'redis') {
            return $this->redisConnected();
        }

        return true;
    }

    private function redisRequired(): bool
    {
        return config('cache.default') === 'redis' || config('session.driver') === 'redis' || config('queue.default') === 'redis';
    }

    private function tableHasRows(string $table): bool
    {
        try {
            return DB::table($table)->exists();
        } catch (\Throwable) {
            return false;
        }
    }

    private function adminUserExists(): bool
    {
        try {
            return User::query()->where('login_id', 'superadmin')->exists();
        } catch (\Throwable) {
            return false;
        }
    }
}

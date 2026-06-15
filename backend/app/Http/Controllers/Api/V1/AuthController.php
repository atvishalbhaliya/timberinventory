<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Models\User;
use App\Services\PermissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(private readonly PermissionService $permissions)
    {
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        $user = User::query()
            ->where('login_id', $credentials['login_id'])
            ->where('status', 'Active')
            ->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'login_id' => ['The provided Login ID or password is incorrect.'],
            ]);
        }

        $token = $user->createToken('erp-api-token')->plainTextToken;

        $roleName = DB::table('roles')->where('id', $user->role_id)->value('name') ?? 'User';
        $branchName = $user->branch_id ? DB::table('branch_master')->where('branch_id', $user->branch_id)->value('branch_name') : null;
        $tenantName = $user->tenant_id ? DB::table('tenant_master')->where('tenant_id', $user->tenant_id)->value('tenant_name') : null;
        $permissionNames = $this->permissions->namesForUser($user)->values();

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'data' => [
                'token' => $token,
                'user' => [
                    'login_id' => $user->login_id,
                    'full_name' => $user->full_name,
                    'employee_code' => $user->employee_code,
                    'mobile' => $user->mobile,
                    'email' => $user->email,
                    'tenant_id' => $user->tenant_id,
                    'tenant_name' => $tenantName,
                    'branch_id' => $user->branch_id,
                    'branch_name' => $branchName,
                    'role_id' => $user->role_id,
                    'role_name' => $roleName,
                    'permissions' => $permissionNames,
                ],
                'flow' => [
                    'login_id_validated' => true,
                    'password_verified' => true,
                    'tenant_loaded' => $user->tenant_id !== null,
                    'branch_loaded' => $user->branch_id !== null,
                    'permissions_loaded' => true,
                    'redirect' => 'dashboard',
                ],
            ],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $roleName = DB::table('roles')->where('id', $user->role_id)->value('name') ?? 'User';
        $branchName = $user->branch_id ? DB::table('branch_master')->where('branch_id', $user->branch_id)->value('branch_name') : null;
        $tenantName = $user->tenant_id ? DB::table('tenant_master')->where('tenant_id', $user->tenant_id)->value('tenant_name') : null;
        $permissionNames = $this->permissions->namesForUser($user)->values();

        return response()->json([
            'success' => true,
            'message' => 'Authenticated user loaded.',
            'data' => [
                'login_id' => $user->login_id,
                'full_name' => $user->full_name,
                'employee_code' => $user->employee_code,
                'mobile' => $user->mobile,
                'email' => $user->email,
                'tenant_id' => $user->tenant_id,
                'tenant_name' => $tenantName,
                'branch_id' => $user->branch_id,
                'branch_name' => $branchName,
                'role_id' => $user->role_id,
                'role_name' => $roleName,
                'permissions' => $permissionNames,
            ],
        ]);
    }

    public function changePassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = $request->user();

        if (! Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Current password is incorrect.'],
            ]);
        }

        $user->forceFill([
            'password' => Hash::make($data['password']),
        ])->save();

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully.',
            'data' => [],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout successful.',
            'data' => [],
        ]);
    }
}

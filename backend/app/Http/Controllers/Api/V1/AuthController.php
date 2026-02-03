<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Jobs\LogAuditEntry;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Handle user login.
     *
     * @param LoginRequest $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function login(LoginRequest $request): JsonResponse
    {
        // Eager load relationships for better performance
        $user = User::with(['roles', 'department'])
            ->where('email', $request->email)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Create token for API authentication
        $token = $user->createToken('auth-token')->plainTextToken;

        // Queue audit log asynchronously (non-blocking)
        LogAuditEntry::dispatch([
            'user_id' => $user->id,
            'action' => 'login',
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'user_role' => $user->roles->first()?->name ?? 'unknown',
            'old_values' => null,
            'new_values' => ['login_at' => now()->toDateTimeString()],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'message' => 'Login successful',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'employee_id' => $user->employee_id,
                'department' => [
                    'id' => $user->department?->id,
                    'name' => $user->department?->name,
                ],
                'roles' => $user->roles->pluck('name'),
            ],
            'token' => $token,
        ]);
    }

    /**
     * Handle user logout.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load('roles');

        // Queue logout audit log asynchronously
        LogAuditEntry::dispatch([
            'user_id' => $user->id,
            'action' => 'logout',
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'user_role' => $user->roles->first()?->name ?? 'unknown',
            'old_values' => null,
            'new_values' => ['logout_at' => now()->toDateTimeString()],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Revoke all tokens
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logout successful',
        ]);
    }

    /**
     * Get authenticated user information.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load(['roles', 'permissions', 'department']);

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'employee_id' => $user->employee_id,
                'department' => [
                    'id' => $user->department?->id,
                    'name' => $user->department?->name,
                ],
                'roles' => $user->roles->pluck('name'),
                'permissions' => $user->getAllPermissions()->pluck('name'),
                'is_god_mode' => $user->hasGodMode(),
            ],
        ]);
    }
}

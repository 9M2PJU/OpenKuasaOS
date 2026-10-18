<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\Org;
use App\Models\OrgMember;
use App\Models\Profile;
use App\Models\User;
use App\Services\Tenancy\OrganizationContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    use ApiResponse;

    /**
     * Register a new user, organization, and owner membership.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        if (! \App\Models\SystemSetting::get('registration_enabled', true)) {
            return $this->error('Pendaftaran akaun baharu ditutup oleh pentadbir sistem.', 'REGISTRATION_DISABLED', null, 403);
        }

        $validated = $request->validated();

        $result = DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            $slug = Str::slug($validated['company_name']) . '-' . Str::random(4);
            $org = Org::create([
                'name' => $validated['company_name'],
                'slug' => $slug,
            ]);

            OrgMember::create([
                'org_id' => $org->id,
                'user_id' => $user->id,
                'role' => 'owner',
            ]);

            Profile::create([
                'user_id' => $user->id,
                'email' => $user->email,
                'full_name' => $user->name,
            ]);

            return ['user' => $user, 'org' => $org];
        });

        $user = $result['user'];
        $org = $result['org'];
        OrganizationContext::set($org);

        $token = $user->createToken('auth-token')->plainTextToken;

        return $this->success([
            'token' => $token,
            'user' => new UserResource($user),
            'organization' => [
                'id' => $org->id,
                'name' => $org->name,
                'slug' => $org->slug,
            ],
        ], 'Pendaftaran berjaya.', null, 201);
    }

    /**
     * Authenticate user and issue Sanctum token.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return $this->error('Maklumat log masuk tidak sah.', 'INVALID_CREDENTIALS', null, 401);
        }

        if ($user->status === 'suspended') {
            return $this->error('Akaun anda telah digantung oleh pentadbir sistem.', 'ACCOUNT_SUSPENDED', null, 403);
        }

        $membership = OrgMember::where('user_id', $user->id)->first();
        if ($membership) {
            OrganizationContext::setId($membership->org_id);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return $this->success([
            'token' => $token,
            'user' => new UserResource($user),
        ], 'Log masuk berjaya.');
    }

    /**
     * Retrieve the current authenticated user and organization session.
     */
    public function viewer(Request $request): JsonResponse
    {
        $user = $request->user();

        return $this->success(
            new UserResource($user),
            'Sesi aktif berjaya diperolehi.'
        );
    }
}

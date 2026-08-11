<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ForgotPasswordRequest;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Http\Resources\Api\AuthenticatedUserResource;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        Password::sendResetLink($request->only('email'));

        return response()->json([
            'message' => 'Bu e-posta adresi kayıtlıysa, şifre sıfırlama bağlantısı gönderilmiştir.',
        ]);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();
        $user = User::query()->where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['E-posta veya parola hatalı.'],
            ]);
        }

        if ($user->banned_at) {
            return response()->json([
                'message' => 'Bu hesap erişime kapatılmıştır.',
            ], 403);
        }

        return $this->tokenResponse($user, $credentials['device_name'] ?? 'TurTube Mobile');
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $attributes = $request->validated();
        $user = User::query()->create([
            'name' => $attributes['name'],
            'email' => $attributes['email'],
            'password' => $attributes['password'],
        ]);

        event(new Registered($user));

        return $this->tokenResponse($user, $attributes['device_name'] ?? 'TurTube Mobile', 201);
    }

    public function logout(Request $request): JsonResponse
    {
        PersonalAccessToken::findToken($request->bearerToken())?->delete();

        return response()->json(null, 204);
    }

    public function me(Request $request): AuthenticatedUserResource
    {
        return new AuthenticatedUserResource($request->user('sanctum'));
    }

    private function tokenResponse(User $user, string $deviceName, int $status = 200): JsonResponse
    {
        $token = $user->createToken(mb_substr($deviceName, 0, 100))->plainTextToken;

        return response()->json([
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => (new AuthenticatedUserResource($user))->resolve(),
            ],
        ], $status);
    }
}

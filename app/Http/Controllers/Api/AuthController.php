<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LoginChallenge;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    private const OTP_TTL_MINUTES = 10;
    private const OTP_MAX_ATTEMPTS = 5;
    private const OTP_RESEND_COOLDOWN_SECONDS = 30;
    private const DEFAULT_DEVICE_NAME = 'dashboard-web';

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:120'],
            'expires_in_days' => ['nullable', 'integer', 'min:1', 'max:365'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials.',
            ], 422);
        }

        [$challenge, $otpCode] = $this->createChallenge(
            request: $request,
            user: $user,
            deviceName: $data['device_name'] ?? self::DEFAULT_DEVICE_NAME
        );

        $payload = [
            'message' => 'Verification code sent. Please confirm OTP to complete login.',
            'two_factor_required' => true,
            'challenge_token' => $challenge->challenge_token,
            'expires_at' => $challenge->expires_at,
        ];

        if ($this->shouldExposeDebugOtp()) {
            $payload['debug_otp'] = $otpCode;
        }

        return response()->json($payload, 202);
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $data = $request->validate([
            'challenge_token' => ['required', 'string', 'max:100'],
            'otp' => ['required', 'regex:/^\d{6}$/'],
            'expires_in_days' => ['nullable', 'integer', 'min:1', 'max:365'],
        ]);

        $challenge = LoginChallenge::with('user')
            ->where('challenge_token', $data['challenge_token'])
            ->first();

        if (! $challenge || $challenge->isConsumed() || $challenge->isExpired()) {
            return response()->json([
                'message' => 'Invalid or expired challenge.',
            ], 422);
        }

        if ($challenge->attempts >= self::OTP_MAX_ATTEMPTS) {
            return response()->json([
                'message' => 'Too many invalid OTP attempts. Please login again.',
            ], 429);
        }

        if (! $challenge->matchesOtp($data['otp'])) {
            $attempts = $challenge->attempts + 1;
            $challenge->forceFill([
                'attempts' => $attempts,
                'consumed_at' => $attempts >= self::OTP_MAX_ATTEMPTS ? now() : null,
            ])->save();

            return response()->json([
                'message' => 'Invalid OTP code.',
            ], 422);
        }

        $challenge->forceFill([
            'attempts' => $challenge->attempts + 1,
            'consumed_at' => now(),
        ])->save();

        if (! $challenge->user) {
            return response()->json([
                'message' => 'User not found for this challenge.',
            ], 422);
        }

        return $this->issueApiToken(
            user: $challenge->user,
            deviceName: $challenge->device_name ?? self::DEFAULT_DEVICE_NAME,
            expiresInDays: $data['expires_in_days'] ?? null
        );
    }

    public function resendOtp(Request $request): JsonResponse
    {
        $data = $request->validate([
            'challenge_token' => ['required', 'string', 'max:100'],
        ]);

        $challenge = LoginChallenge::with('user')
            ->where('challenge_token', $data['challenge_token'])
            ->first();

        if (! $challenge || $challenge->isConsumed() || $challenge->isExpired()) {
            return response()->json([
                'message' => 'Invalid or expired challenge.',
            ], 422);
        }

        if ($challenge->last_sent_at && $challenge->last_sent_at->diffInSeconds(now()) < self::OTP_RESEND_COOLDOWN_SECONDS) {
            return response()->json([
                'message' => 'Please wait a few seconds before requesting another OTP.',
            ], 429);
        }

        $otpCode = $this->generateOtpCode();
        $challenge->forceFill([
            'otp_hash' => $this->hashOtp($otpCode),
            'attempts' => 0,
            'last_sent_at' => now(),
            'expires_at' => now()->addMinutes(self::OTP_TTL_MINUTES),
        ])->save();

        if ($challenge->user) {
            $this->deliverOtpCode($challenge->user, $otpCode, $challenge);
        }

        $payload = [
            'message' => 'OTP code resent successfully.',
            'challenge_token' => $challenge->challenge_token,
            'expires_at' => $challenge->expires_at,
        ];

        if ($this->shouldExposeDebugOtp()) {
            $payload['debug_otp'] = $otpCode;
        }

        return response()->json($payload);
    }

    public function logout(Request $request): JsonResponse
    {
        $tokenId = $request->attributes->get('api_token_id');

        if ($tokenId) {
            $request->user()->tokens()->whereKey($tokenId)->delete();
        }

        return response()->json([
            'message' => 'Logout successful.',
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $request->user(),
        ]);
    }

    private function issueApiToken(User $user, string $deviceName, ?int $expiresInDays = null): JsonResponse
    {
        $plainToken = Str::random(80);
        $expiresAt = $expiresInDays ? now()->addDays($expiresInDays) : null;

        $token = $user->tokens()->create([
            'name' => $deviceName,
            'token_hash' => hash('sha256', $plainToken),
            'expires_at' => $expiresAt,
            'last_used_at' => now(),
        ]);

        return response()->json([
            'message' => 'Login successful.',
            'token' => $plainToken,
            'token_type' => 'Bearer',
            'expires_at' => $token->expires_at,
            'user' => $user,
        ], 201);
    }

    /**
     * @return array{0: LoginChallenge, 1: string}
     */
    private function createChallenge(Request $request, User $user, string $deviceName): array
    {
        $otpCode = $this->generateOtpCode();

        // Keep only one active challenge per user/device to avoid confusion.
        LoginChallenge::where('user_id', $user->id)
            ->where('device_name', $deviceName)
            ->whereNull('consumed_at')
            ->delete();

        $challenge = LoginChallenge::create([
            'user_id' => $user->id,
            'challenge_token' => Str::random(64),
            'otp_hash' => $this->hashOtp($otpCode),
            'device_name' => $deviceName,
            'attempts' => 0,
            'last_sent_at' => now(),
            'expires_at' => now()->addMinutes(self::OTP_TTL_MINUTES),
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 1000, ''),
        ]);

        $this->deliverOtpCode($user, $otpCode, $challenge);

        return [$challenge, $otpCode];
    }

    private function generateOtpCode(): string
    {
        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    private function hashOtp(string $otpCode): string
    {
        return hash('sha256', $otpCode.'|'.config('app.key'));
    }

    private function deliverOtpCode(User $user, string $otpCode, LoginChallenge $challenge): void
    {
        $subject = 'Code de verification - Sports Club Display';
        $body = "Bonjour {$user->name},\n\nVotre code de verification est : {$otpCode}\nCe code expire dans ".self::OTP_TTL_MINUTES." minutes.\n\nSi ce n'est pas vous, ignorez ce message.";

        try {
            Mail::raw($body, function ($message) use ($user, $subject): void {
                $message->to($user->email)->subject($subject);
            });
        } catch (\Throwable $e) {
            Log::warning('OTP mail delivery failed', [
                'challenge_id' => $challenge->id,
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function shouldExposeDebugOtp(): bool
    {
        return app()->environment('local') || (bool) config('app.debug');
    }
}

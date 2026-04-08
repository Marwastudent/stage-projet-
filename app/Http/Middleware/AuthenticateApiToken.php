<?php

namespace App\Http\Middleware;

use App\Models\ApiToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiToken
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $plainToken = $request->bearerToken();

        if (! $plainToken) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $token = ApiToken::with('user')->where('token_hash', hash('sha256', $plainToken))->first();

        if (! $token || $token->isExpired()) {
            return response()->json([
                'message' => 'Invalid or expired token.',
            ], 401);
        }

        $token->forceFill(['last_used_at' => now()])->save();

        $request->attributes->set('api_token_id', $token->id);
        $request->setUserResolver(static fn () => $token->user);

        return $next($request);
    }
}

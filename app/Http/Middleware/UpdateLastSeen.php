<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class UpdateLastSeen
{
    /**
     * Stamp the authenticated user's last_seen_at, throttled to at most once
     * per minute via the cache so we don't write on every request. This powers
     * the admin "online/offline" indicator. Any failure here must never break
     * the request, so the update is best-effort and swallowed.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null) {
            $cacheKey = 'last-seen:' . $user->id;

            if (! Cache::has($cacheKey)) {
                Cache::put($cacheKey, true, now()->addMinutes(1));

                try {
                    // updateQuietly avoids touching `updated_at` / firing events.
                    $user->forceFill(['last_seen_at' => now()])->saveQuietly();
                } catch (\Throwable $e) {
                    // Never let presence tracking interfere with the request.
                    report($e);
                }
            }
        }

        return $next($request);
    }
}

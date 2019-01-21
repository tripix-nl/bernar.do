<?php

namespace App\CacheProfiles;

use DateTime;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Spatie\ResponseCache\CacheProfiles\CacheProfile;
use Symfony\Component\HttpFoundation\Response;

class NoAuthCacheProfile implements CacheProfile
{
    public function shouldCacheRequest(Request $request): bool
    {
        if ($request->ajax()) {
            return false;
        }

        if ($this->isRunningInConsole()) {
            return false;
        }

        return $request->isMethod('get');
    }

    public function shouldCacheResponse(Response $response): bool
    {
        return $response->isSuccessful() || $response->isRedirection();
    }

    public function enabled(Request $request): bool
    {
        return config('responsecache.enabled');
    }

    public function cacheRequestUntil(Request $request): DateTime
    {
        return Carbon::now()->addMinutes(
            config('responsecache.cache_lifetime_in_minutes')
        );
    }

    public function cacheNameSuffix(Request $request): string
    {
        return '';
    }

    public function isRunningInConsole(): bool
    {
        if (app()->environment('testing')) {
            return false;
        }

        return app()->runningInConsole();
    }
}

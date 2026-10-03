<?php

namespace App\Http\Middleware;

use App\Models\Merchant;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateMerchant
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->header('X-API-Key');
        abort_unless($key, 401, 'Missing API key');

        $hash = hash('sha256', $key);
        $merchant = Cache::remember("merchant:key:$hash", 300,
            fn () => Merchant::where('api_key_hash', $hash)->first());

        abort_unless($merchant, 401, 'Invalid API key');
        $request->attributes->set('merchant', $merchant);

        return $next($request);
    }
}

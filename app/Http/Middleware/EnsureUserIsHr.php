<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsHr
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->isHr()) {
            abort(403, 'Only HR can access this page.');
        }

        return $next($request);
    }
}

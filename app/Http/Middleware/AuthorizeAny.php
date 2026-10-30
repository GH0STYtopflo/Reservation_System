<?php

namespace App\Http\Middleware;

use Closure;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthorizeAny
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$abilities): Response
    {
        foreach ($abilities as $ability) {
            if (Gate::allows($ability)) {
                return $next($request);
            }
        }

        abort(403);
    }
}

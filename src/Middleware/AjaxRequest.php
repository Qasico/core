<?php

namespace Core\Middleware;

use Closure;

class AjaxRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     * @param  string|null              $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return $next($request);
        }

        return response('Unauthorized.', 401);
    }
}

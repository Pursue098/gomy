<?php

namespace App\Http\Middleware;

use Closure;

class Teia
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (! $request->user()->isTeia()) {
            if ($request->ajax() || $request->wantsJson() || $request->is('api/v*')) {
                return response()->json([
                    'error' => 'Unauthorized',
                ], \Symfony\Component\HttpFoundation\Response::HTTP_UNAUTHORIZED);
            } else {
                return back()->with('message', ['type' => 'danger', 'text' => 'Unauthorized request!']);
            }
        }

        return $next($request);
    }
}

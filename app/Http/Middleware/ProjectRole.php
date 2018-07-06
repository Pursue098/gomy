<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Auth;
use Closure;

class ProjectRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(\Illuminate\Http\Request $request, Closure $next, $role, $guard = null)
    {
        // Controllo che l'utente abbia il livello richiesto
        if (! $request->project->{str_plural($role)}->contains($request->user()) && ! $request->user()->isTeia()) {
            if ($request->ajax() || $request->wantsJson() || $request->is('api/v*')) {
                return response()->json([
                    'error' => 'Unauthorized',
                ], \Symfony\Component\HttpFoundation\Response::HTTP_UNAUTHORIZED);
            } else {
                return back()->with('message', ['type' => 'danger', 'text' => 'Unauthorized request!']);
            }
        }

        // controllo che il canale sia abilitato e configurato
        if (isset($request->channel)) {
            if (! isset(\App\Channel::$supported[$request->channel->type]) || ! \App\Channel::$supported[$request->channel->type]['enabled']) {
                if ($request->ajax() || $request->wantsJson() || $request->is('api/v*')) {
                    return response()->json([
                        'error' => 'Channel not supported',
                    ], \Symfony\Component\HttpFoundation\Response::HTTP_FORBIDDEN);
                } else {
                    return back()->with('message', ['type' => 'danger', 'text' => 'Channel not supported!']);
                }
            } else {
                if ($request->channel->status == 'new' && ! $request->fullUrlIs(route('channel.configure', [$request->project, $request->channel]) . '*')) {
                    return redirect()->route('channel.configure', [$request->project, $request->channel])->with('message', ['type' => 'warning', 'text' => 'Channel is not configured.']);
                }
            }
        }

        return $next($request);
    }
}

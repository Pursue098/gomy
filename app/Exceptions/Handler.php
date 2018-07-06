<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Illuminate\Session\TokenMismatchException::class,
        \Illuminate\Validation\ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
            \Log::error('ERROR 404 at this URL:');
            \Log::error(\Request::fullUrl());
        }

        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        /*
        if ( $exception->getStatusCode() == 403) { 
             \Session::flash('Exwarning-message', "You are not allowed to visit that." );
            return redirect()->route('projects.index');
        }
        */
        
        
        if ($this->isHttpException($exception) ) {
        
            $status = $exception->getStatusCode();
            if($status == 403)
            {
                \Session::flash('warning-message', "You are not allowed to visit that." );
                return redirect()->route('projects.index');
            }
        
        
        }
        if ($exception instanceof \Jrean\UserVerification\Exceptions\UserNotVerifiedException) {
            return response()->view('laravel-user-verification::not-verified', [], 403)
                ->header('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        }

        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
             if ($request->expectsJson() || $request->is('api/v*')) {
                return response()->json(['error' => 'Not found.'], 404);
            }
        }

        return parent::render($request, $exception);
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Illuminate\Http\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson() || $request->is('api/v*')) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        return redirect()->guest('login');
    }
}

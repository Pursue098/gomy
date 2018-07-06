<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class VerifyCsrfToken extends BaseVerifier
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        '/facebook/webhook/callback',
        '/facebook/tab',
        '/woocommerce/callback/*',
        '/woocommerce/webhook/*',
        '/api/v*',
        '/login',
        '/mobimesh/*',
        '/chatfuel',
        'stripe/*',
    ];
}

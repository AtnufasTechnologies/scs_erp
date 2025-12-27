<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'webhook',             // If Easebuzz is posting to your /webhook
        'erp/student/payment-success',     // If they POST to your success URL
        'erp/student/payment-failure'      // If they POST to your failure URL
    ];
}

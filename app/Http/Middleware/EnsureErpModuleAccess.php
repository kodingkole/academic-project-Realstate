<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureErpModuleAccess
{
    public function handle(Request $request, Closure $next, ?string $module = null): Response
    {
        abort_unless($request->user()?->role === 'admin', 403, 'This ERP module requires administrator access.');
        $request->attributes->set('erp_module', $module ?? 'dashboard');
        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use App\Models\AccountCompanyInfo;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompany
{
    public function handle(Request $request, Closure $next): Response
    {
        $account = $request->user();
        abort_unless($account instanceof AccountCompanyInfo && $account->status === 'active', 403);

        return $next($request);
    }
}

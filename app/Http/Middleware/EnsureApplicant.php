<?php

namespace App\Http\Middleware;

use App\Models\AccountUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApplicant
{
    public function handle(Request $request, Closure $next): Response
    {
        $account = $request->user();
        abort_unless($account instanceof AccountUser, 403);

        return $next($request);
    }
}

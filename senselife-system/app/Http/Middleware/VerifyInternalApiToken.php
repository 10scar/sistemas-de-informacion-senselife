<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyInternalApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = config('services.internal_api.token');
        $provided = $request->header('x-internal-token');

        if ($expected === null || $expected === '' || $provided !== $expected) {
            return response()->json(['message' => 'Token interno inválido o ausente.'], 401);
        }

        return $next($request);
    }
}

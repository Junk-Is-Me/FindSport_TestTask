<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class ApiTokenAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $header = $request->header('Authorization');

        if (!$header || strpos($header, 'Bearer ') !== 0) {
            return response()->json(['message' => 'Unathorized'], 401);
        }

        $token = substr($header, 7);

        $user = User::where('api_token', $token)->first();

        if (!$user) {
            return response()->json(['message' => 'Unathorized'], 401);
        }

        auth()->setUser($user);

        return $next($request);
    }
}

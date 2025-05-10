<?php

namespace App\Http\Middleware;

use App\ResponseTrait;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthStudent
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    use ResponseTrait;
    public function handle(Request $request, Closure $next): Response
    {
        $teacher = auth('api-student')->user();

        if (! $teacher) {
            return $this->returnError("Unauthorized. Please login.");
        }

        return $next($request);
    }
}

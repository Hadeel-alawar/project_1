<?php

namespace App\Http\Middleware;

use App\ResponseTrait;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class verifyStudentEmail
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    use ResponseTrait;
    public function handle(Request $request, Closure $next): Response
    {
        $student = auth('api-student')->user();
    
        if (! $student) {
            return $this->returnError("Unauthorized.");
        }
    
        if (! $student->is_email_verified) {
            return $this->returnError("Email not verified.");
        }
    
        return $next($request);
    }
}

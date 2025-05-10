<?php

namespace App\Http\Middleware;

use App\ResponseTrait;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\VarDumper\Exception\ThrowingCasterException;

class verifyTeacherEmail
{
    use ResponseTrait;
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $teacher = auth('api-teacher')->user();
    
        if (! $teacher) {
            return $this->returnError("Unauthorized.");
        }
    
        if (! $teacher->is_email_verified) {
            return $this->returnError("Email not verified.");
        }
    
        return $next($request);
    }
    
}

<?php

namespace App\Http\Middleware;

use Closure;

class extendsSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        config()->set('session.lifetime', '525600'); 
    
        return $next($request);
    }
}

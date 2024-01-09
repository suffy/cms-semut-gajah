<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class DistributorMiddleware
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
        if (Auth::user())
        {
            // if ($request->user() && $request->user()->account_type == '1') {
            //     return $next($request);
            // }
            if ($request->user() && ($request->user()->account_role == 'distributor' || $request->user()->account_role == 'distributor_ho')) {
                return $next($request);
            }

            return redirect('/');
        }

        return redirect('/login');
    }
}

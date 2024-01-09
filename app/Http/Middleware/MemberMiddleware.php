<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class MemberMiddleware
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
            if ($request->user() && $request->user()->account_type == '4') {
                return $next($request);
            }

            return redirect('/');
        }

        return redirect('/member/login');


    }
}

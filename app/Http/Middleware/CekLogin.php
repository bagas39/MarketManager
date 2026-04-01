<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class CekLogin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Session::has('user_role')) {
            return redirect('/login')->with('error', 'Akses ditolak! Silakan login terlebih dahulu.');
        }
        
        return $next($request);
    }
}
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check() || !Auth::user()->is_admin) {
            // 管理者じゃなかったら管理者用ログインページにリダイレクト
            return redirect()->route('admin.login.show');
        }
        return $next($request);
    }
}

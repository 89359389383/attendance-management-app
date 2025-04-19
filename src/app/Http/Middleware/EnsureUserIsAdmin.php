<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next)
    {
        // 現在のURLが /admin で始まる場合のみadmin認証を強制
        if ($request->is('admin/*') || $request->is('stamp_correction_request/*')) {
            if (!auth()->check() || !auth()->user()->is_admin) {
                return redirect('/admin/login')->with('error', '管理者のみアクセス可能です');
            }
        }

        return $next($request);
    }
}

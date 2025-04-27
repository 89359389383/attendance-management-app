<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            // URLに "admin" が含まれていたら管理者ログイン画面へ
            if ($request->is('admin/*')) {
                return route('admin.login.show');
            }
            // それ以外は一般ユーザー用ログイン画面へ
            return route('login.show');
        }
    }
}

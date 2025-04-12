<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class Handler extends ExceptionHandler
{
    protected $dontReport = [];

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $exception)
    {
        // ログインユーザー情報
        $user = Auth::check() ? [
            'id' => Auth::user()->id,
            'name' => Auth::user()->name,
            'email' => Auth::user()->email,
        ] : 'Guest';

        // **エラー時のログ（重要な情報のみ記録）**
        if ($exception) {
            Log::error('【例外キャッチ】', [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'url' => $request->url(),
                'method' => $request->method(),
                'ip_address' => $request->ip(),
                'params' => $request->all(),
                'user' => $user,
                'status_code' => $exception instanceof \Symfony\Component\HttpKernel\Exception\HttpException ? $exception->getStatusCode() : 500,
                'env_config' => [
                    'APP_ENV' => env('APP_ENV'), // **エラー時のみ記録**
                    'SESSION_DRIVER' => env('SESSION_DRIVER'),
                    'CACHE_DRIVER' => env('CACHE_DRIVER'),
                ],
                'trace' => $exception->getTraceAsString(),
            ]);
        }

        return parent::render($request, $exception);
    }
}

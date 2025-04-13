<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title', 'COACHTECHフリマ')</title>
    <link rel="stylesheet" href="{{ asset('css/common.css') }}" />
    @yield('css')
</head>

<body>
    <!-- ヘッダー -->
    <header class="header">
        <div class="header-left">
            <div class="logo-image">
                <img src="{{ asset('storage/logo.svg') }}" alt="COACHTECH Logo">
            </div>
        </div>

        <!-- ログイン・会員登録ページ、メール認証ページでは非表示 -->
        @if (
        !Request::is('login') &&
        !Request::is('register') &&
        !Request::is('email/verify') &&
        !Request::is('admin/auth/login') &&
        !Request::is('admin/auth/register')
        )

        @if (Request::is('attendance*') || Request::is('attendance_request*'))
        <!-- 一般ユーザー向けメニュー -->
        <div class="header-right">
            <a href="{{ route('user.show') }}" class="header-link">勤怠</a>
            <a href="{{ route('user.show') }}" class="header-link">勤怠一覧</a>
            <a href="{{ route('items.create') }}" class="header-button">申請</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="header-link">ログアウト</button>
            </form>
        </div>

        @elseif (Request::is('admin/attendance*') || Request::is('admin/attendance_request*') || Request::is('admin/staff*'))
        <!-- 管理者向けメニュー -->
        <div class="header-right">
            <a href="{{ route('user.show') }}" class="header-link">勤怠一覧</a>
            <a href="{{ route('user.show') }}" class="header-link">スタッフ一覧</a>
            <a href="{{ route('items.create') }}" class="header-button">申請一覧</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="header-link">ログアウト</button>
            </form>
        </div>
        @endif
    </header>

    <main>
        @yield('content')
    </main>
</body>

</html>
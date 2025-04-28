<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title', 'COACHTECH勤怠管理')</title>
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
        !Request::is('admin/login')
        )
        @if (auth()->check() && auth()->user()->is_admin)
        {{-- 管理者向けメニュー --}}
        <div class="header-right">
            <a href="{{ route('admin.attendance.list') }}" class="header-link">勤怠一覧</a>
            <a href="{{ route('admin.staff.list') }}" class="header-link">スタッフ一覧</a>
            <a href="{{ route('admin.request.list') }}" class="header-link">申請一覧</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="header-link">ログアウト</button>
            </form>
        </div>
        @else
        {{-- 一般ユーザー向けメニュー --}}
        <div class="header-right">
            <a href="{{ route('attendance.show') }}" class="header-link">勤怠</a>
            <a href="{{ route('attendance.list') }}" class="header-link">勤怠一覧</a>
            <a href="{{ route('request.list') }}" class="header-link">申請</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="header-link">ログアウト</button>
            </form>
        </div>
        @endif
        @endif
    </header>

    <main>
        @yield('content')
    </main>

    @yield('js')
</body>

</html>
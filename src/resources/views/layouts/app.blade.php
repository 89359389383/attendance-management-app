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
        @if (!Request::is('login') && !Request::is('register') && !Request::is('email/verify'))
        <div class="header-center">
            <!-- 現在のタブに応じて検索フォームの遷移先を変更 -->
            <form method="GET" action="{{ ($tab ?? '') == 'mylist' ? route('items.index') : route('items.search') }}">
                <input type="text" name="name" value="{{ request('name') }}" placeholder="なにをお探しですか？" class="search-input">
                <button type="submit" class="search-button">検索</button>
            </form>
        </div>
        <div class="header-right">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="header-link">ログアウト</button>
            </form>
            <a href="{{ route('user.show') }}" class="header-link">マイページ</a>
            <a href="{{ route('items.create') }}" class="header-button">出品</a>
        </div>
        @endif
    </header>

    <main>
        @yield('content')
    </main>
</body>

</html>
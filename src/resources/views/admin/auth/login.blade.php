@extends('layouts.app')

@section('title', 'COACHTECH勤怠管理 - 管理者ログイン')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/login.css') }}" />
@endsection

@section('content')
<div class="container">
    <h1>管理者ログイン</h1>
    <form action="{{ route('admin.login') }}" method="POST">
        @csrf

        {{-- ▼ここに認証エラー用のメッセージを表示 --}}
        @if ($errors->has('email') && $errors->first('email') === 'ログイン情報が登録されていません')
        <p class="error-message" style="color: red; margin-bottom: 10px;">
            {{ $errors->first('email') }}
        </p>
        @endif

        <div class="form-group">
            <label for="email">メールアドレス</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}">
            {{-- ▼バリデーションエラーのみ表示（ログイン失敗以外） --}}
            @error('email')
            @if ($message !== 'ログイン情報が登録されていません')
            <p class="error-message" style="color: red;">
                {!! nl2br(e($message)) !!}
            </p>
            @endif
            @enderror
        </div>

        <div class="form-group">
            <label for="password">パスワード</label>
            <input type="password" id="password" name="password">
            @error('password')
            <p class="error-message" style="color: red;">
                {{ $message }}
            </p>
            @enderror
        </div>

        <button type="submit" class="submit-button">管理者ログインする</button>
    </form>
</div>
@endsection
@extends('layouts.app')

@section('title', 'COACHTECH勤怠管理 - 会員登録')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/register.css') }}" />
@endsection

@section('content')
<div class="container">
    <h1>会員登録</h1>
    <form action="{{ route('register.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="name">名前</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}">
            @error('name')
            <p class="error-message" style="color: red;">
                {{ $message }}
            </p>
            @enderror
        </div>

        <div class="form-group">
            <label for="email">メールアドレス</label>
            <input type="text" id="email" name="email" value="{{ old('email') }}">
            @error('email')
            <p class="error-message" style="color: red;">
                {!! nl2br(e($message)) !!}
            </p>
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

        <div class="form-group">
            <label for="password_confirmation">パスワード確認</label>
            <input type="password" id="password_confirmation" name="password_confirmation">
            @error('password_confirmation')
            <p class="error-message" style="color: red;">
                {{ $message }}
            </p>
            @enderror
        </div>

        <button type="submit" class="submit-button">登録する</button>

        <a href="{{ route('login.show') }}" class="login-link">ログインはこちら</a>
    </form>
</div>
@endsection
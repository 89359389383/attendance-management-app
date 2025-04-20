@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/verify-email.css') }}" />
@endsection

@section('content')
<div class="container">
    <div class="message">
        <p>登録していただいたメールアドレスに認証メールを送付しました。</p>
        <p>メール認証を完了してください。</p>
    </div>

    @if (session('message'))
    <p class="alert alert-success">{{ session('message') }}</p>
    @endif

    @if (session('resent'))
    <div class="alert alert-success" role="alert">
        {{ __('新しい認証リンクをメールで送信しました。') }}
    </div>
    @endif

    　　<div class="mt-3">
        <a href="http://localhost:8025" class="btn-primary">認証はこちらから</a>
    </div>

    <form method="POST" action="{{ route('verification.resend') }}">
        @csrf
        <button type="submit" class="btn-secondary">
            {{ __('認証メールを再送する') }}
        </button>
    </form>
</div>
@endsection
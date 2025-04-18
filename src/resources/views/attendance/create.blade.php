@extends('layouts.app')

@section('title', '勤怠打刻')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/create.css') }}">
@endsection

@section('content')
<div class="container">
    {{-- 日付・時刻を現在日時で表示 --}}
    <div class="date">{{ \Carbon\Carbon::now()->format('Y年n月j日(D)') }}</div>
    <div class="time">{{ \Carbon\Carbon::now()->format('H:i') }}</div>

    {{-- 勤怠ステータスを表示 --}}
    <div class="status-badge">
        ステータス: {{ $attendance->status ?? '勤務外' }}
    </div>

    {{-- フラッシュメッセージ表示 --}}
    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @elseif(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- 勤怠打刻フォーム --}}
    <form action="{{ route('attendance.store') }}" method="POST">
        @csrf

        {{-- 出勤ボタン：ステータスが勤務外かつ clock_in がまだ未入力のときのみ表示 --}}
        @if(($attendance->status ?? '勤務外') === '勤務外' && empty($attendance->clock_in))
        <button type="submit" name="action" value="clock_in" class="button">出勤</button>
        @endif

        {{-- 休憩開始：ステータスが出勤中なら常に表示（何回でもOK） --}}
        @if(($attendance->status ?? '') === '出勤中')
        <button type="submit" name="action" value="break_start" class="button">休憩入</button>
        @endif

        {{-- 休憩戻り：ステータスが休憩中なら常に表示（何回でもOK） --}}
        @if(($attendance->status ?? '') === '休憩中')
        <button type="submit" name="action" value="break_end" class="button">休憩戻</button>
        @endif

        {{-- 退勤：ステータスが出勤中かつ clock_out がまだ未入力のときのみ表示 --}}
        @if(($attendance->status ?? '') === '出勤中' && empty($attendance->clock_out))
        <button type="submit" name="action" value="clock_out" class="button">退勤</button>
        @endif

        {{-- ステータスが退勤済みの場合はメッセージを表示 --}}
        @if(($attendance->status ?? '') === '退勤済')
        <div class="message">お疲れ様でした。</div>
        @endif
    </form>
</div>
@endsection
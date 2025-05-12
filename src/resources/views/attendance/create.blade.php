@extends('layouts.app')

@section('title', 'COACHTECH勤怠管理 - 勤怠打刻')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/create.css') }}">
@endsection

@section('content')
<div class="container">
    {{-- 勤怠ステータスを表示 --}}
    <div class="status-badge">
        {{ $attendance->status ?? '勤務外' }}
    </div>

    {{-- 日付・時刻を現在日時で表示 --}}
    <div class="date">
        @php
        $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
        $now = \Carbon\Carbon::now();
        @endphp
        {{ $now->format('Y年n月j日') }}({{ $weekdays[$now->dayOfWeek] }})
    </div>

    <div class="time" id="current-time">{{ \Carbon\Carbon::now()->format('H:i:s') }}</div>

    {{-- 勤怠打刻フォーム --}}
    <form action="{{ route('attendance.store') }}" method="POST">
        @csrf

        {{-- 出勤ボタン：ステータスが勤務外かつ clock_in がまだ未入力のときのみ表示 --}}
        @if(($attendance->status ?? '勤務外') === '勤務外' && empty($attendance->clock_in))
        <button type="submit" name="action" value="clock_in" class="button">出勤</button>

        {{-- ★【追加】打刻忘れ防止メッセージ --}}
        <div class="alert-message">
            本日まだ出勤打刻がされていません。<br>忘れずに打刻してください。
        </div>
        @endif

        {{-- 退勤：ステータスが出勤中かつ clock_out がまだ未入力のときのみ表示 --}}
        @if(($attendance->status ?? '') === '出勤中' && empty($attendance->clock_out))
        <button type="submit" name="action" value="clock_out" class="button-clock-out">退勤</button>
        @endif

        {{-- 休憩開始：ステータスが出勤中なら常に表示（何回でもOK） --}}
        @if(($attendance->status ?? '') === '出勤中')
        <button type="submit" name="action" value="break_start" class="button-break">休憩入</button>
        @endif

        {{-- 休憩戻り：ステータスが休憩中なら常に表示（何回でもOK） --}}
        @if(($attendance->status ?? '') === '休憩中')
        <button type="submit" name="action" value="break_end" class="button-break-back">休憩戻</button>
        @endif

        {{-- ステータスが退勤済みの場合はメッセージを表示 --}}
        @if(($attendance->status ?? '') === '退勤済')
        <div class="message">お疲れ様でした。</div>
        @endif
    </form>
</div>
@endsection

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        function updateTime() {
            const now = new Date();
            const hours = now.getHours().toString().padStart(2, '0');
            const minutes = now.getMinutes().toString().padStart(2, '0');
            const seconds = now.getSeconds().toString().padStart(2, '0');
            const currentTime = `${hours}:${minutes}:${seconds}`;
            document.getElementById('current-time').textContent = currentTime;
        }

        // 初回実行
        updateTime();
        // 1秒ごとに更新
        setInterval(updateTime, 1000);
    });
</script>
@endsection
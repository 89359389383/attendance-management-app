@extends('layouts.app')

@section('title', 'COACHTECH勤怠管理 - 勤怠一覧（管理者）')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance/index.css') }}">
@endsection

@section('content')
<div class="container">
    <h1 class="title">{{ \Carbon\Carbon::parse($date)->format('Y年m月d日') }}の勤怠一覧</h1>

    <!-- 日付切り替えナビゲーション -->
    <div class="date-nav">
        <a href="{{ route('admin.attendance.list', ['date' => \Carbon\Carbon::parse($date)->subDay()->format('Y-m-d'), 'name' => request('name')]) }}" class="date-nav-btn">
            <span class="arrow">←</span>前日
        </a>

        <!-- カレンダー入力 -->
        <form method="GET" action="{{ route('admin.attendance.list') }}" id="dateForm" style="display: inline-block; position: relative;">
            <!-- 隠しinput -->
            <input type="hidden" name="name" value="{{ request('name') }}"> {{-- 検索キーワード保持 --}}
            <input
                type="date"
                name="date"
                id="dateInput"
                value="{{ $date }}"
                style="opacity: 0; position: absolute; left: 0; top: 0; width: 32px; height: 32px; cursor: pointer;"
                onchange="updateDateAndSubmit(this.value)">

            <!-- アイコンと日付表示エリア -->
            <div
                style="display: inline-flex; align-items: center; gap: 10px; font-size: 18px; cursor: pointer;"
                onclick="document.getElementById('dateInput').showPicker()">
                📅
                <span id="selectedDate" class="selected-date-text">{{ \Carbon\Carbon::parse($date)->format('Y/m/d') }}</span>
            </div>
        </form>

        <a href="{{ route('admin.attendance.list', ['date' => \Carbon\Carbon::parse($date)->addDay()->format('Y-m-d'), 'name' => request('name')]) }}" class="date-nav-btn">
            翌日<span class="arrow">→</span>
        </a>
    </div>

    <form method="GET" class="search-form" style="margin: 20px 0; display: flex; gap: 10px;">
        {{-- 現在の日付を維持する --}}
        <input type="hidden" name="date" value="{{ request('date', $date) }}">

        {{-- 名前での検索 --}}
        <input type="text" name="name" value="{{ request('name') }}" placeholder="名前で検索" class="search-input">

        {{-- 検索ボタン --}}
        <button type="submit" class="search-button">検索</button>

        {{-- リセットボタン（dateは保持してnameのみリセット）--}}
        <a href="{{ route('admin.attendance.list', ['date' => request('date', $date)]) }}" class="search-button" style="background-color: #ccc; text-decoration: none; padding: 6px 12px; border-radius: 4px;">
            リセット
        </a>
    </form>

    <!-- 勤怠情報テーブル -->
    <table class="attendance-table">
        <thead>
            <tr>
                <th>名前</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($attendances as $attendance)
            <tr>
                <td>{{ $attendance->user->name }}</td>
                <td>{{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}</td>
                <td>{{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}</td>
                <td>
                    @php
                    $totalBreak = $attendance->breakTimes->sum(function ($break) {
                    if ($break->break_start && $break->break_end) {
                    $start = \Carbon\Carbon::parse($break->break_start);
                    $end = \Carbon\Carbon::parse($break->break_end);
                    return $end->diffInMinutes($start);
                    }
                    return 0;
                    });
                    echo sprintf('%d:%02d', floor($totalBreak / 60), $totalBreak % 60);
                    @endphp
                </td>
                <td>
                    @php
                    $workTime = '';
                    if ($attendance->clock_in && $attendance->clock_out) {
                    $total = \Carbon\Carbon::parse($attendance->clock_out)->diffInMinutes($attendance->clock_in) - $totalBreak;
                    $workTime = floor($total / 60) . ':' . str_pad($total % 60, 2, '0', STR_PAD_LEFT);
                    }
                    echo $workTime;
                    @endphp
                </td>
                <td>
                    <a href="{{ route('admin.attendance.detail', $attendance->id) }}" class="detail-link">詳細</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6">該当する勤怠データはありません。</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

<script>
    function updateDateAndSubmit(value) {
        // 選んだ日付を表示用に更新
        const date = new Date(value);
        const formatted = date.getFullYear() + '/' +
            String(date.getMonth() + 1).padStart(2, '0') + '/' +
            String(date.getDate()).padStart(2, '0');
        document.getElementById('selectedDate').innerText = formatted;

        // フォームを自動送信
        document.getElementById('dateForm').submit();
    }
</script>
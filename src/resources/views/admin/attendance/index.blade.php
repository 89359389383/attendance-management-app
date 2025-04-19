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
        <a href="{{ route('admin.attendance.list', ['date' => \Carbon\Carbon::parse($date)->subDay()->format('Y-m-d')]) }}" class="date-nav-btn">前日</a>

        <!-- カレンダー入力 -->
        <form method="GET" action="{{ route('admin.attendance.list') }}" style="display: inline-block;">
            <input type="date" name="date" value="{{ $date }}">
            <button type="submit">表示</button>
        </form>

        <a href="{{ route('admin.attendance.list', ['date' => \Carbon\Carbon::parse($date)->addDay()->format('Y-m-d')]) }}" class="date-nav-btn">翌日</a>
    </div>

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
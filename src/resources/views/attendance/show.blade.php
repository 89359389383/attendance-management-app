@extends('layouts.app')

@section('title', '勤怠詳細')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/show.css') }}">
@endsection

@section('content')
<div class="container">
    <h1>勤怠詳細</h1>

    {{-- バリデーションエラー表示 --}}
    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            {{-- 重複エラーメッセージを1行だけ表示するように修正 --}}
            @foreach (array_unique($errors->all()) as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- ▼ セッションに成功メッセージがある場合に表示 --}}
    @if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    {{-- ▼ 勤怠修正申請フォーム（出勤・退勤・休憩・備考を送信） --}}
    {{-- 対応要件: FN027〜FN030 --}}
    @if ($attendanceRequest && $attendanceRequest->status === '承認待ち')
    <p style="color: red;">承認待ちのため修正はできません。</p>
    <div class="card">
        <div class="row">
            <div class="label">名前</div>
            <div class="content name-content">
                <span class="user-name">{{ $attendance->user->name }}</span>
            </div>
        </div>

        <div class="row">
            <div class="label">日付</div>
            @php
            $date = \Carbon\Carbon::parse($attendance->work_date);
            @endphp
            <div class="content date-content">
                <span class="year">{{ $date->year }}年</span>
                <span class="month-day">{{ $date->format('n月j日') }}</span>
            </div>
        </div>

        <div class="row">
            <div class="label">出勤・退勤</div>
            <div class="content">
                <div class="time-range">
                    <input type="time" value="{{ optional($attendanceRequest->clock_in)->format('H:i') }}" disabled>
                    <span class="time-separator">～</span>
                    <input type="time" value="{{ optional($attendanceRequest->clock_out)->format('H:i') }}" disabled>
                </div>
            </div>
        </div>

        @foreach ($attendanceRequest->requestBreakTimes as $index => $break)
        <div class="row">
            <div class="label">休憩{{ $index + 1 }}</div>
            <div class="content">
                <div class="time-range">
                    <input type="time" value="{{ optional($break->break_start)->format('H:i') }}" disabled>
                    <span>～</span>
                    <input type="time" value="{{ optional($break->break_end)->format('H:i') }}" disabled>
                </div>
            </div>
        </div>
        @endforeach

        <div class="row">
            <div class="label">備考</div>
            <div class="remarks-content">
                <textarea disabled>{{ $attendanceRequest->note }}</textarea>
            </div>
        </div>
    </div>
    @else
    {{-- 承認待ちでない場合は通常の修正フォームを表示 --}}
    <form action="{{ route('attendance.update', $attendance->id) }}" method="POST">
        @csrf
        @method('PUT') {{-- PUTメソッドで更新処理を行う --}}

        <div class="card">
            <div class="row">
                <div class="label">名前</div>
                <div class="content name-content">
                    <span class="user-name">{{ $attendance->user->name }}</span>
                </div>
            </div>

            <div class="row">
                <div class="label">日付</div>
                @php
                $date = \Carbon\Carbon::parse($attendance->work_date);
                @endphp
                <div class="content date-content">
                    <span class="year">{{ $date->year }}年</span>
                    <span class="month-day">{{ $date->format('n月j日') }}</span>
                </div>
            </div>

            <div class="row">
                <div class="label">出勤・退勤</div>
                <div class="content">
                    <div class="time-range">
                        <input type="time" name="clock_in" value="{{ old('clock_in', optional($attendance->clock_in)->format('H:i')) }}">
                        <span>～</span>
                        <input type="time" name="clock_out" value="{{ old('clock_out', optional($attendance->clock_out)->format('H:i')) }}">
                    </div>
                </div>
            </div>

            @foreach ($attendance->breakTimes as $index => $break)
            <div class="row">
                <div class="label">休憩{{ $index + 1 }}</div>
                <div class="content">
                    <div class="time-range">
                        <input type="time" name="break_start[]" value="{{ optional($break->break_start)->format('H:i') }}">
                        <span>～</span>
                        <input type="time" name="break_end[]" value="{{ optional($break->break_end)->format('H:i') }}">
                    </div>
                </div>
            </div>
            @endforeach

            <div class="row">
                <div class="label">休憩{{ count($attendance->breakTimes) + 1 }}</div>
                <div class="content">
                    <div class="time-range">
                        <input type="time" name="break_start[]" value="">
                        <span>～</span>
                        <input type="time" name="break_end[]" value="">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="label">備考</div>
                <div class="remarks-content">
                    <textarea name="note">{{ old('note', $attendance->note) }}</textarea>
                </div>
            </div>
        </div>

        <div class="button-container">
            <button type="submit" class="edit-button">修正</button>
        </div>
    </form>
    @endif
</div>
@endsection
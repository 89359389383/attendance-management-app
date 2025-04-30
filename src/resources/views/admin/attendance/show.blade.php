@extends('layouts.app')

@section('title', 'COACHTECH - 勤怠詳細（管理者）')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance/show.css') }}">
@endsection

@section('content')
<div class="container">
    <h1 class="title">勤怠詳細</h1>

    {{-- エラーメッセージ --}}
    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach (array_unique($errors->all()) as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- 成功メッセージ --}}
    @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if (isset($attendanceRequest) && $attendanceRequest->status === '承認待ち')
    {{-- ▼ 承認待ちのため修正不可 --}}
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
                    <span>～</span>
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
    {{-- ▼ 通常の修正フォーム --}}
    <form action="{{ route('admin.attendance.update', $attendance->id) }}" method="POST">
        @csrf
        @method('PUT')

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
            <button type="submit" class="approve-button">修正</button>
        </div>
    </form>
    @endif
</div>
@endsection
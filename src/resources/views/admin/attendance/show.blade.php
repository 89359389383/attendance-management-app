@extends('layouts.app')

@section('title', 'COACHTECH - 勤怠詳細（管理者）')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance/show.css') }}">
@endsection

@section('content')
<div class="container">
    <h1 class="title">勤怠詳細（管理者）</h1>

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

    {{-- 勤怠修正フォーム --}}
    <form action="{{ route('admin.attendance.update', $attendance->id) }}" method="POST">
        @csrf
        @method('PUT')

        <table class="attendance-table">
            <tr>
                <td>名前</td>
                <td>{{ $attendance->user->name }}</td>
            </tr>
            <tr>
                <td>日付</td>
                <td>{{ \Carbon\Carbon::parse($attendance->work_date)->format('Y年n月j日') }}</td>
            </tr>
            <tr>
                <td>出勤・退勤</td>
                <td>
                    <input type="time" name="clock_in" value="{{ old('clock_in', optional($attendance->clock_in)->format('H:i')) }}">
                    ～
                    <input type="time" name="clock_out" value="{{ old('clock_out', optional($attendance->clock_out)->format('H:i')) }}">
                </td>
            </tr>

            {{-- 休憩時間（複数対応） --}}
            @foreach ($attendance->breakTimes as $index => $break)
            <tr>
                <td>休憩{{ $index + 1 }}</td>
                <td>
                    <input type="time" name="break_start[]" value="{{ old("break_start.$index", optional($break->break_start)->format('H:i')) }}">
                    ～
                    <input type="time" name="break_end[]" value="{{ old("break_end.$index", optional($break->break_end)->format('H:i')) }}">
                </td>
            </tr>
            @endforeach

            {{-- 空の休憩追加欄（1枠） --}}
            <tr>
                <td>休憩{{ count($attendance->breakTimes) + 1 }}</td>
                <td>
                    <input type="time" name="break_start[]" value="">
                    ～
                    <input type="time" name="break_end[]" value="">
                </td>
            </tr>

            {{-- 備考欄 --}}
            <tr>
                <td>備考</td>
                <td>
                    <textarea name="note">{{ old('note', $attendance->note) }}</textarea>
                </td>
            </tr>
        </table>

        {{-- 修正ボタン --}}
        <div class="button-container">
            <button type="submit" class="edit-button">修正</button>
        </div>
    </form>
</div>
@endsection
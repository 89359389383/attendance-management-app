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
    <form action="{{ route('attendance.update', $attendance->id) }}" method="POST">
        @csrf
        @method('PUT') {{-- PUTメソッドで更新処理を行う --}}

        <div class="card">

            {{-- ▼ 氏名の表示（ログインユーザーと一致） --}}
            {{-- 対応要件: FN026-1 --}}
            <div class="row">
                <div class="label">名前</div>
                <div class="content">{{ $attendance->user->name }}</div>
            </div>

            {{-- ▼ 日付の表示（選択した勤怠の勤務日） --}}
            {{-- 対応要件: FN026-2 --}}
            <div class="row">
                <div class="label">日付</div>
                <div class="content">{{ \Carbon\Carbon::parse($attendance->work_date)->format('Y年n月j日') }}</div>
            </div>

            {{-- ▼ 出勤・退勤時刻の入力欄 --}}
            {{-- 対応要件: FN027, FN028, FN029 --}}
            <div class="row">
                <div class="label">出勤・退勤</div>
                <div class="content">
                    <div class="time-range">
                        {{-- 出勤時刻の入力 --}}
                        <input type="time" name="clock_in" value="{{ old('clock_in', optional($attendance->clock_in)->format('H:i')) }}">
                        <span>～</span>
                        {{-- 退勤時刻の入力 --}}
                        <input type="time" name="clock_out" value="{{ old('clock_out', optional($attendance->clock_out)->format('H:i')) }}">
                    </div>
                </div>
            </div>

            {{-- ▼ 登録済みの休憩時間を表示・編集 --}}
            {{-- 対応要件: FN026-3, FN027 --}}
            @foreach ($attendance->breakTimes as $index => $break)
            <div class="row">
                <div class="label">休憩{{ $index + 1 }}</div>
                <div class="content">
                    <div class="time-range">
                        {{-- 休憩開始時刻 --}}
                        <input type="time" name="break_start[]" value="{{ optional($break->break_start)->format('H:i') }}">
                        <span>～</span>
                        {{-- 休憩終了時刻 --}}
                        <input type="time" name="break_end[]" value="{{ optional($break->break_end)->format('H:i') }}">
                    </div>
                </div>
            </div>
            @endforeach

            {{-- ▼ 追加用の休憩時間フィールド（未入力欄） --}}
            {{-- 対応要件: FN026-4 --}}
            <div class="row">
                <div class="label">休憩{{ count($attendance->breakTimes) + 1 }}</div>
                <div class="content">
                    <div class="time-range">
                        {{-- 新規休憩開始時刻 --}}
                        <input type="time" name="break_start[]" value="">
                        <span>～</span>
                        {{-- 新規休憩終了時刻 --}}
                        <input type="time" name="break_end[]" value="">
                    </div>
                </div>
            </div>

            {{-- ▼ 備考欄（必須項目） --}}
            {{-- 対応要件: FN027, FN028, FN029 --}}
            <div class="row">
                <div class="label">備考</div>
                <div class="remarks-content">
                    {{-- 既存の備考 or 入力内容を表示 --}}
                    <textarea name="note">{{ old('note', $attendance->note) }}</textarea>
                </div>
            </div>

        </div>

        {{-- ▼ 修正ボタン（修正申請を送信） --}}
        {{-- 対応要件: FN030 --}}
        <div class="button-container">
            <button type="submit" class="edit-button">修正</button>
        </div>
    </form>
</div>
@endsection
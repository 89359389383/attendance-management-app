@extends('layouts.app')

@section('title', 'COACHTECH - 修正申請詳細（承認）')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance_request/approve.css') }}">
@endsection

@section('content')
<div class="container">
    <h1 class="title">修正申請詳細（承認）</h1>

    {{-- 成功・エラー表示 --}}
    @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- 修正申請の詳細表示テーブル --}}
    <table class="attendance-table">
        <tr>
            <td>名前</td>
            <td>{{ $request->user->name }}</td>
        </tr>
        <tr>
            <td>日付</td>
            <td>{{ \Carbon\Carbon::parse($request->attendance->work_date)->format('Y年n月j日') }}</td>
        </tr>
        <tr>
            <td>出勤・退勤</td>
            <td>
                {{ \Carbon\Carbon::parse($request->clock_in)->format('H:i') }}
                <span class="time-separator">～</span>
                {{ \Carbon\Carbon::parse($request->clock_out)->format('H:i') }}
            </td>
        </tr>

        {{-- 修正申請された休憩時間を表示（複数対応） --}}
        @foreach ($request->requestBreakTimes as $index => $break)
        <tr>
            <td>休憩{{ $index + 1 }}</td>
            <td>
                {{ \Carbon\Carbon::parse($break->break_start)->format('H:i') }}
                <span class="time-separator">～</span>
                {{ \Carbon\Carbon::parse($break->break_end)->format('H:i') }}
            </td>
        </tr>
        @endforeach

        <tr>
            <td>備考</td>
            <td>{{ $request->note }}</td>
        </tr>

        @if($request->status === 'approved')
        <tr>
            <td>承認者</td>
            <td>{{ optional($request->approvedByUser)->name }}</td>
        </tr>
        <tr>
            <td>承認日時</td>
            <td>{{ \Carbon\Carbon::parse($request->approved_at)->format('Y年n月j日 H:i') }}</td>
        </tr>
        @endif
    </table>

    {{-- 承認ボタン（承認済みでない場合のみ表示） --}}
    @if($request->status === '承認待ち')
    <div class="button-container">
        <form method="POST" action="{{ route('admin.request.approve', $request->id) }}">
            @csrf
            <button type="submit" class="approve-button">承認する</button>
        </form>
    </div>
    @else
    <div class="button-container">
        <button class="approve-button" disabled>承認済み</button>
    </div>
    @endif
</div>
@endsection
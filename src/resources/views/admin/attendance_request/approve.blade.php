@extends('layouts.app')

@section('title', 'COACHTECH - 修正申請詳細（承認）')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance_request/approve.css') }}">
@endsection

@section('content')
<div class="container">
    <h1 class="title">勤怠詳細</h1>

    {{-- 成功・エラー表示 --}}
    @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- 修正申請の詳細表示 --}}
    <div class="card">

        <div class="row">
            <div class="label">名前</div>
            <div class="content">{{ $request->user->name }}</div>
        </div>

        <div class="row">
            <div class="label">日付</div>
            <div class="content">{{ \Carbon\Carbon::parse($request->attendance->work_date)->format('Y年n月j日') }}</div>
        </div>

        <div class="row">
            <div class="label">出勤・退勤</div>
            <div class="content">
                {{ \Carbon\Carbon::parse($request->clock_in)->format('H:i') }}
                <span class="time-separator">～</span>
                {{ \Carbon\Carbon::parse($request->clock_out)->format('H:i') }}
            </div>
        </div>

        {{-- 修正申請された休憩時間を表示（複数対応） --}}
        @foreach ($request->requestBreakTimes as $index => $break)
        <div class="row">
            <div class="label">休憩{{ $index + 1 }}</div>
            <div class="content">
                {{ \Carbon\Carbon::parse($break->break_start)->format('H:i') }}
                <span class="time-separator">～</span>
                {{ \Carbon\Carbon::parse($break->break_end)->format('H:i') }}
            </div>
        </div>
        @endforeach

        <div class="row">
            <div class="label">備考</div>
            <div class="content">
                {{ $request->note }}
            </div>
        </div>

        @if($request->status === 'approved')
        <div class="row">
            <div class="label">承認者</div>
            <div class="content">
                {{ optional($request->approvedByUser)->name }}
            </div>
        </div>

        <div class="row">
            <div class="label">承認日時</div>
            <div class="content">
                {{ \Carbon\Carbon::parse($request->approved_at)->format('Y年n月j日 H:i') }}
            </div>
        </div>
        @endif

    </div>

    {{-- 承認ボタン --}}
    <div class="button-container">
        @if($request->status === '承認待ち')
        <form method="POST" action="{{ route('admin.request.approve', $request->id) }}">
            @csrf
            <button type="submit" class="approve-button">承認する</button>
        </form>
        @else
        <button class="approve-button-approved" disabled>承認済み</button>
        @endif
    </div>

</div>
@endsection
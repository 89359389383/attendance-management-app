@extends('layouts.app')

@section('title', '勤怠詳細')

@section('css')
<link rel="stylesheet" href="{{ asset('css/styles.css') }}">
@endsection

@section('content')
<div class="container">
    <h1>勤怠詳細</h1>
    <div class="card">
        <div class="row">
            <div class="label">名前</div>
            <div class="content">西　怜奈</div>
        </div>
        <div class="row">
            <div class="label">日付</div>
            <div class="content">2023年　　　　　6月1日</div>
        </div>
        <div class="row">
            <div class="label">出勤・退勤</div>
            <div class="content">
                <div class="time-range">
                    <input type="text" value="09:00">
                    <span>～</span>
                    <input type="text" value="20:00">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="label">休憩</div>
            <div class="content">
                <div class="time-range">
                    <input type="text" value="12:00">
                    <span>～</span>
                    <input type="text" value="13:00">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="label">休憩2</div>
            <div class="content">
                <div class="time-range">
                    <input type="text" value="">
                    <span>～</span>
                    <input type="text" value="">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="label">備考</div>
            <div class="remarks-content">
                <textarea></textarea>
            </div>
        </div>
    </div>
    <div class="button-container">
        <button class="edit-button">修正</button>
    </div>
</div>
@endsection
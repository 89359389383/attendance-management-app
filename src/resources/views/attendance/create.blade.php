@extends('layouts.app')

@section('title', '休憩タイマー')

@section('css')
<link rel="stylesheet" href="{{ asset('css/styles.css') }}">
@endsection

@section('content')
<div class="container">
    <div class="status-badge">休憩中</div>
    <div class="date">2023年6月1日(木)</div>
    <div class="time">08:00</div>
    <button class="button">休憩戻</button>
</div>
@endsection
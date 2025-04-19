@extends('layouts.app')

@section('title', 'COACHTECH - 勤怠詳細')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance_request/approve.css') }}">
@endsection

@section('content')
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>勤怠詳細</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <div class="container">
        <h1 class="title">勤怠詳細</h1>

        <table class="attendance-table">
            <tr>
                <td>名前</td>
                <td>西　怜奈</td>
            </tr>
            <tr>
                <td>日付</td>
                <td>2023年　　　　　　　　6月1日</td>
            </tr>
            <tr>
                <td>出勤・退勤</td>
                <td>09:00　　　　<span class="time-separator">～</span>　　　　18:00</td>
            </tr>
            <tr>
                <td>休憩</td>
                <td>12:00　　　　<span class="time-separator">～</span>　　　　13:00</td>
            </tr>
            <tr>
                <td>休憩2</td>
                <td></td>
            </tr>
            <tr>
                <td>備考</td>
                <td>電車遅延のため</td>
            </tr>
        </table>

        <div class="button-container">
            <button class="approve-button">承認済み</button>
        </div>
    </div>
</body>

</html>
@endsection
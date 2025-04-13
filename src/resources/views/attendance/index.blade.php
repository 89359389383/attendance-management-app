@extends('layouts.app')

@section('title', 'COACHTECH - 勤怠管理システム')

@section('css')
<link rel="stylesheet" href="{{ asset('css/styles.css') }}"> <!-- 外部CSSファイルをリンク -->
@endsection

@section('content')
<body>
    <header>
        <div class="logo">
            <span class="logo-ct">CT</span>&nbsp;COACHTECH
        </div>
        <div class="nav-links">
            <a href="#">勤怠</a>
            <a href="#">勤怠一覧</a>
            <a href="#">申請</a>
            <a href="#">ログアウト</a>
        </div>
    </header>

    <div class="container">
        <h1 class="title">勤怠一覧</h1>

        <div class="month-selector">
            <div class="month-nav">
                <span>← 前月</span>
            </div>
            <div class="month-display">
                <span class="calendar-icon">📅</span>
                <span>2023/06</span>
            </div>
            <div class="month-nav">
                <span>翌月 →</span>
            </div>
        </div>

        <table class="attendance-table">
            <thead>
                <tr>
                    <th>日付</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>06/01(木)</td>
                    <td>09:00</td>
                    <td>18:00</td>
                    <td>1:00</td>
                    <td>8:00</td>
                    <td><a href="#" class="detail-link">詳細</a></td>
                </tr>
                <tr>
                    <td>06/02(金)</td>
                    <td>09:00</td>
                    <td>18:00</td>
                    <td>1:00</td>
                    <td>8:00</td>
                    <td><a href="#" class="detail-link">詳細</a></td>
                </tr>
                <tr>
                    <td>06/03(土)</td>
                    <td>09:00</td>
                    <td>18:00</td>
                    <td>1:00</td>
                    <td>8:00</td>
                    <td><a href="#" class="detail-link">詳細</a></td>
                </tr>
                <tr>
                    <td>06/04(日)</td>
                    <td>09:00</td>
                    <td>18:00</td>
                    <td>1:00</td>
                    <td>8:00</td>
                    <td><a href="#" class="detail-link">詳細</a></td>
                </tr>
                <tr>
                    <td>06/05(月)</td>
                    <td>09:00</td>
                    <td>18:00</td>
                    <td>1:00</td>
                    <td>8:00</td>
                    <td><a href="#" class="detail-link">詳細</a></td>
                </tr>
                <tr>
                    <td>06/06(火)</td>
                    <td>09:00</td>
                    <td>18:00</td>
                    <td>1:00</td>
                    <td>8:00</td>
                    <td><a href="#" class="detail-link">詳細</a></td>
                </tr>
                <tr>
                    <td>06/07(水)</td>
                    <td>09:00</td>
                    <td>18:00</td>
                    <td>1:00</td>
                    <td>8:00</td>
                    <td><a href="#" class="detail-link">詳細</a></td>
                </tr>
                <tr>
                    <td>06/08(木)</td>
                    <td>09:00</td>
                    <td>18:00</td>
                    <td>1:00</td>
                    <td>8:00</td>
                    <td><a href="#" class="detail-link">詳細</a></td>
                </tr>
                <tr>
                    <td>06/09(金)</td>
                    <td>09:00</td>
                    <td>18:00</td>
                    <td>1:00</td>
                    <td>8:00</td>
                    <td><a href="#" class="detail-link">詳細</a></td>
                </tr>
                <tr>
                    <td>06/10(土)</td>
                    <td>09:00</td>
                    <td>18:00</td>
                    <td>1:00</td>
                    <td>8:00</td>
                    <td><a href="#" class="detail-link">詳細</a></td>
                </tr>
            </tbody>
        </table>
    </div>
</body>
@endsection

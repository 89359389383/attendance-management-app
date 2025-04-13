<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COACHTECH 勤怠管理システム</title>
    <link rel="stylesheet" href="styles.css"> <!-- 外部CSSファイルをリンク -->
</head>

<body>
    <header>
        <div class="logo">
            <span class="logo-icon">CT</span>COACHTECH
        </div>
        <nav>
            <ul>
                <li><a href="#">勤怠一覧</a></li>
                <li><a href="#">スタッフ一覧</a></li>
                <li><a href="#">申請一覧</a></li>
                <li><a href="#">ログアウト</a></li>
            </ul>
        </nav>
    </header>

    <div class="container">
        <h1 class="title">2023年6月1日の勤怠</h1>

        <div class="date-nav">
            <a href="#" class="date-nav-btn prev">前日</a>
            <div class="current-date">2023/06/01</div>
            <a href="#" class="date-nav-btn next">翌日</a>
        </div>

        <table class="attendance-table">
            <thead>
                <tr>
                    <th>名前</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>山田 太郎</td>
                    <td>09:00</td>
                    <td>18:00</td>
                    <td>1:00</td>
                    <td>8:00</td>
                    <td><a href="#" class="detail-btn">詳細</a></td>
                </tr>
                <tr>
                    <td>西 怜奈</td>
                    <td>09:00</td>
                    <td>18:00</td>
                    <td>1:00</td>
                    <td>8:00</td>
                    <td><a href="#" class="detail-btn">詳細</a></td>
                </tr>
                <tr>
                    <td>増田 一世</td>
                    <td>09:00</td>
                    <td>18:00</td>
                    <td>1:00</td>
                    <td>8:00</td>
                    <td><a href="#" class="detail-btn">詳細</a></td>
                </tr>
                <tr>
                    <td>山本 敏吉</td>
                    <td>09:00</td>
                    <td>18:00</td>
                    <td>1:00</td>
                    <td>8:00</td>
                    <td><a href="#" class="detail-btn">詳細</a></td>
                </tr>
                <tr>
                    <td>秋田 朋美</td>
                    <td>09:00</td>
                    <td>18:00</td>
                    <td>1:00</td>
                    <td>8:00</td>
                    <td><a href="#" class="detail-btn">詳細</a></td>
                </tr>
                <tr>
                    <td>中西 敦夫</td>
                    <td>09:00</td>
                    <td>18:00</td>
                    <td>1:00</td>
                    <td>8:00</td>
                    <td><a href="#" class="detail-btn">詳細</a></td>
                </tr>
            </tbody>
        </table>
    </div>
</body>

</html>
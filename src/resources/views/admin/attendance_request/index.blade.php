<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>申請一覧</title>
    <link rel="stylesheet" href="styles.css"> <!-- 外部CSSをリンク -->
</head>

<body>
    <div class="container">
        <h1>申請一覧</h1>

        <div class="tabs">
            <div class="tab active">承認待ち</div>
            <div class="tab">承認済み</div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>状態</th>
                    <th>名前</th>
                    <th>対象日時</th>
                    <th>申請理由</th>
                    <th>申請日時</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>承認待ち</td>
                    <td>西俗奈</td>
                    <td>2023/06/01</td>
                    <td>遅延のため</td>
                    <td>2023/06/02</td>
                    <td><button class="details-btn">詳細</button></td>
                </tr>
                <tr>
                    <td>承認待ち</td>
                    <td>山田太郎</td>
                    <td>2023/06/01</td>
                    <td>遅延のため</td>
                    <td>2023/08/02</td>
                    <td><button class="details-btn">詳細</button></td>
                </tr>
                <tr>
                    <td>承認待ち</td>
                    <td>山田花子</td>
                    <td>2023/06/02</td>
                    <td>遅延のため</td>
                    <td>2023/07/02</td>
                    <td><button class="details-btn">詳細</button></td>
                </tr>
            </tbody>
        </table>
    </div>
</body>

</html>
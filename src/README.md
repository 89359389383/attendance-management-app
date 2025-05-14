# 勤怠管理アプリ

## 環境構築

### Docker ビルド

1.  git clone <リポジトリのリンク>
2.  docker-compose up -d --build

＊ MySQL は OS によって起動しない場合があります。その場合は、docker-compose.yml ファイルを編集し、それぞれの PC に合わせて調整してください。

### Laravel 環境構築

1.  docker-compose exec php bash
2.  composer install
3.  .env.example をコピーして.env ファイルを作成し、環境変数を変更<br>
    ※.env ファイルの DB_DATABASE、DB_USERNAME、DB_PASSWORD の値を docker-compose.yml に記載の値に変更
4.  php artisan key:generate
5.  php artisan migrate
6.  php artisan db:seed<br>
    ※ログインの際必要なデータは database\seeders\DatabaseSeeder.php に記載<br>
    ※勤怠データは2025年1月～4月分挿入
7.  php artisan storage:link

## 使用技術

-   PHP: 8.4.1
-   Laravel: 8.83.8
-   MySQL: 8.0.26
-   mailhog

## 補足

-   パスの設定(管理者側の勤怠詳細画面、申請一覧画面)<br>/attendance/{id}→/admin/attendance/{id}<br>/stamp_correction_request/list→/admin/stamp_correction_request/list<br>に設定する<br>
    ※一般ユーザーと管理者がそれぞれ同じ パス を使う場合、認可ミドルウェアの不一致やビューの切り替え失敗などの不具合が発生する可能性があるので、ルーティングやミドルウェアによって管理者専用の処理を明確に分けるため明示的なパス分離を指定
-   一般ユーザーの勤怠修正申請後の挙動<br> ※修正申請後管理者が承認する前に再度修正を送付するケースを想定して申請後も承認待ちのページから再度修正して申請できるように設定

## URL

-   開発環境: [http://localhost/](http://localhost/)
-   phpMyAdmin: [http://localhost:8080/](http://localhost:8080/)

## ER 図

![勤怠管理ER図](https://github.com/user-attachments/assets/f80b494e-7971-46da-8c4a-c3c0d63f9729)

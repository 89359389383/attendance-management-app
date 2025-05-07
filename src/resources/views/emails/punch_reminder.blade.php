<!DOCTYPE html>
<html>

<body>
    <p>{{ $user->name }}さん、おはようございます。</p>
    <p>本日は出勤日です。8:50時点でまだ打刻がされていないようです。</p>
    <p>お手数ですが、出勤打刻をお願いします。</p>
    <p><a href="{{ url('/attendance') }}">打刻はこちら</a></p>
</body>

</html>
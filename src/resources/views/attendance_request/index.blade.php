@extends('layouts.app')

@section('title', 'COACHTECH - 申請一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance_request/index.css') }}"> <!-- 外部CSSをリンク -->
@endsection

@section('content')
<div class="container">
    <h1>申請一覧</h1>

    <!-- タブ切り替え -->
    <div class="tabs">
        <button class="tab-button active" onclick="showTab('pending')">承認待ち</button>
        <button class="tab-button" onclick="showTab('approved')">承認済み</button>
    </div>

    <!-- 承認待ちリスト -->
    <div id="pending" class="tab-content">
        <table>
            <thead>
                <tr>
                    <th>状態</th>
                    <th>名前</th>
                    <th>対象日</th>
                    <th>申請理由</th>
                    <th>申請日</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pendingRequests as $request)
                <tr>
                    <td>承認待ち</td>
                    <td>{{ $request->user->name }}</td>
                    <td>{{ \Carbon\Carbon::parse($request->attendance->work_date)->format('Y/m/d') }}</td>
                    <td>{{ $request->note }}</td>
                    {{-- 変更後：PHP関数で処理（どんな形式でも安全） --}}
                    <td>{{ \Carbon\Carbon::parse($request->request_date)->format('Y/m/d') }}</td>
                    <td>
                        <a href="{{ route('attendance.detail', ['id' => $request->attendance_id]) }}" class="detail-link">詳細</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">承認待ちの申請はありません。</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- 承認済みリスト -->
    <div id="approved" class="tab-content" style="display: none;">
        <table>
            <thead>
                <tr>
                    <th>状態</th>
                    <th>名前</th>
                    <th>対象日</th>
                    <th>申請理由</th>
                    <th>申請日</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($approvedRequests as $request)
                <tr>
                    <td>承認済み</td>
                    <td>{{ $request->user->name }}</td>
                    <td>{{ \Carbon\Carbon::parse($request->attendance->work_date)->format('Y/m/d') }}</td>
                    <td>{{ $request->note }}</td>
                    <td>{{ $request->request_date->format('Y/m/d') }}</td>
                    <td>
                        <a href="{{ route('attendance.detail', ['id' => $request->attendance_id]) }}" class="detail-link">詳細</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">承認済みの申請はありません。</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- タブ切り替えスクリプト -->
<script>
    function showTab(tabName) {
        document.querySelectorAll('.tab-content').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.tab-button').forEach(el => el.classList.remove('active'));
        document.getElementById(tabName).style.display = 'block';
        event.target.classList.add('active');
    }
</script>
@endsection
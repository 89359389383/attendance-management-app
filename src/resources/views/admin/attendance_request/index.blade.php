@extends('layouts.app')

@section('title', 'COACHTECH - 修正申請一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance_request/index.css') }}"> {{-- 外部CSS --}}
@endsection

@section('content')
<div class="container">
    <h1>修正申請一覧</h1>

    {{-- タブ切り替え --}}
    <div class="tabs">
        <button class="tab-button active" onclick="showTab('pending')">承認待ち</button>
        <button class="tab-button" onclick="showTab('approved')">承認済み</button>
    </div>

    {{-- 承認待ち一覧 --}}
    <div id="pending" class="tab-content">
        {{-- 一括承認用フォーム --}}
        <form method="POST" action="{{ route('admin.request.bulk_approve') }}">
            @csrf
            <table>
                <thead>
                    <tr>
                        <th>選択</th>
                        <th>状態</th>
                        <th>名前</th>
                        <th>対象日時</th>
                        <th>申請理由</th>
                        <th>申請日時</th>
                        <th>詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pendingRequests as $request)
                    <tr>
                        <td><input type="checkbox" name="request_ids[]" value="{{ $request->id }}"></td>
                        <td>承認待ち</td>
                        <td>{{ $request->user->name }}</td>
                        <td>{{ \Carbon\Carbon::parse($request->attendance->work_date)->format('Y/m/d') }}</td>
                        <td>{{ $request->note }}</td>
                        <td>{{ \Carbon\Carbon::parse($request->request_date)->format('Y/m/d') }}</td>
                        <td>
                            {{-- 修正申請詳細へ遷移するボタン --}}
                            <a href="{{ route('admin.request.show', ['id' => $request->id]) }}" class="detail-link">詳細</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7">承認待ちの申請はありません。</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- 一括承認ボタン --}}
            <div style="margin-top: 10px;">
                <button type="submit" class="btn btn-primary">選択した申請を一括承認する</button>
            </div>
        </form>
    </div>

    {{-- 承認済み一覧 --}}
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
                    <td>{{ \Carbon\Carbon::parse($request->request_date)->format('Y/m/d') }}</td>
                    <td>
                        {{-- 承認済みの詳細表示（承認ボタンは出ない） --}}
                        <a href="{{ route('admin.request.show', ['id' => $request->id]) }}" class="detail-link">詳細</a>
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

{{-- タブ切り替え用スクリプト --}}
<script>
    function showTab(tabName) {
        document.querySelectorAll('.tab-content').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.tab-button').forEach(el => el.classList.remove('active'));
        document.getElementById(tabName).style.display = 'block';
        event.target.classList.add('active');
    }
</script>
@endsection
@extends('layouts.app')

@section('title', 'COACHTECH勤怠管理 - 修正申請一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance_request/index.css') }}"> {{-- 外部CSS --}}
@endsection

@section('content')
<div class="container">
    <h1>修正申請一覧</h1>

    {{-- タブ切り替え＆検索 --}}
    <div class="tabs">
        @php
        $pendingUrl = '?tab=pending&name=' . urlencode(request('name'));
        $approvedUrl = '?tab=approved&name=' . urlencode(request('name'));
        @endphp

        <button class="tab-button {{ request('tab', 'pending') === 'pending' ? 'active' : '' }}"
            onclick="location.href='{{ $pendingUrl }}'">
            承認待ち
        </button>

        <button class="tab-button {{ request('tab') === 'approved' ? 'active' : '' }}"
            onclick="location.href='{{ $approvedUrl }}'">
            承認済み
        </button>

        {{-- 検索フォーム --}}
        <form method="GET" class="search-form" style="margin: 20px 0; display: flex; gap: 10px;">
            {{-- 名前検索 --}}
            <input type="text" name="name" value="{{ request('name') }}" placeholder="名前で検索" class="search-input">

            {{-- 現在のタブ情報を引き継ぐ hidden input --}}
            <input type="hidden" name="tab" value="{{ request('tab', 'pending') }}">

            {{-- 検索ボタン --}}
            <button type="submit" class="search-button">検索</button>

            {{-- リセットボタン（tab情報も維持） --}}
            <a href="{{ route(Route::currentRouteName(), ['tab' => request('tab', 'pending')]) }}"
                class="search-button"
                style="background-color: #e4e4e4; text-decoration: none; padding: 6px 12px; border-radius: 4px;">
                リセット
            </a>
        </form>
    </div>

    {{-- 承認待ち一覧 --}}
    <div id="pending" class="tab-content">
        {{-- 一括承認用フォーム --}}
        <form method="POST" action="{{ route('admin.request.bulk_approve') }}">
            @csrf
            <table>
                <thead>
                    <tr>
                        {{-- 一括承認ボタン --}}
                        <div>
                            <button type="submit" class="btn-success">選択した申請を一括承認する</button>
                        </div>
                        <th><input type="checkbox" id="select-all"></th> {{-- 全選択用チェックボックス --}}
                        <th>状態</th>
                        <th>{!! sortLink('名前', 'name', request('sort'), request('direction'), 'pending') !!}</th>
                        <th>{!! sortLink('対象日時', 'work_date', request('sort'), request('direction'), 'pending') !!}</th>
                        <th>申請理由</th>
                        <th>{!! sortLink('申請日時', 'request_date', request('sort'), request('direction'), 'pending') !!}</th>
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
        </form>
    </div>

    {{-- 承認済み一覧 --}}
    <div id="approved" class="tab-content" style="display: none;">
        <table>
            <thead>
                <tr>
                    <th>状態</th>
                    <th>{!! sortLink('名前', 'name', request('sort'), request('direction'), 'approved') !!}</th>
                    <th>{!! sortLink('対象日', 'work_date', request('sort'), request('direction'), 'approved') !!}</th>
                    <th>申請理由</th>
                    <th>{!! sortLink('申請日時', 'request_date', request('sort'), request('direction'), 'approved') !!}</th>
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
    document.addEventListener("DOMContentLoaded", function() {
        const urlParams = new URLSearchParams(window.location.search);
        const tab = urlParams.get('tab') || 'pending';
        showTab(tab);

        // 全選択／全解除トグル機能
        const selectAllCheckbox = document.getElementById('select-all');
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('input[name="request_ids[]"]');
                checkboxes.forEach(cb => cb.checked = selectAllCheckbox.checked);
            });
        }
    });

    function showTab(tabName) {
        document.querySelectorAll('.tab-content').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.tab-button').forEach(el => el.classList.remove('active'));
        document.getElementById(tabName).style.display = 'block';
        document.querySelectorAll('.tab-button').forEach(btn => {
            if (btn.textContent.includes(tabName === 'pending' ? '承認待ち' : '承認済み')) {
                btn.classList.add('active');
            }
        });
    }
</script>

@endsection
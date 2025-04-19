@extends('layouts.app')

@section('title', 'COACHTECH勤怠管理 - スタッフ一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/staff/index.css') }}">
@endsection

@section('content')
<div class="container">
    <h1 class="page-title">スタッフ一覧</h1>

    <table class="staff-table">
        <thead>
            <tr>
                <th>名前</th>
                <th>メールアドレス</th>
                <th>月次勤怠</th>
            </tr>
        </thead>
        <tbody>
            {{-- 一般ユーザー一覧をループ表示 --}}
            @forelse ($staff as $user)
            <tr>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>
                    {{-- 詳細ボタン：月次勤怠一覧へのリンク（ルート名を利用） --}}
                    <a href="{{ route('admin.attendance.staff', ['id' => $user->id]) }}" class="detail-link">詳細</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="3">表示するスタッフが存在しません。</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
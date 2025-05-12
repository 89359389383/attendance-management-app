@extends('layouts.app')

@section('title', 'COACHTECH勤怠管理 - スタッフ月次勤怠一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance/staff_monthly.css') }}">
@endsection

@section('content')
<div class="container">
    <h1 class="title">{{ $user->name }}さんの勤怠一覧</h1>

    <!-- 月切り替えナビゲーション -->
    <div class="month-selector">
        <div class="month-nav">
            <a href="{{ route('admin.attendance.staff', ['id' => $user->id, 'month' => \Carbon\Carbon::parse($yearMonth)->copy()->subMonth()->format('Y-m')]) }}"
                class="month-link">
                <span class="arrow">←</span>前月
            </a>
        </div>
        <div class="month-display" onclick="toggleMonthModal()" style="cursor: pointer;">
            <span class="calendar-icon">📅</span>
            <span>{{ \Carbon\Carbon::parse($yearMonth)->format('Y年m月') }}</span>
        </div>
        <div class="month-nav">
            <a href="{{ route('admin.attendance.staff', ['id' => $user->id, 'month' => \Carbon\Carbon::parse($yearMonth)->copy()->addMonth()->format('Y-m')]) }}"
                class="month-link">
                翌月<span class="arrow">→</span>
            </a>
        </div>
    </div>

    <!-- CSV出力ボタン -->
    <div class="csv-export">
        <form method="GET" action="{{ route('admin.attendance.staff.export', ['id' => $user->id]) }}">
            <input type="hidden" name="month" value="{{ $yearMonth }}">
            <button type="submit" class="btn btn-primary">CSV出力</button>
        </form>
    </div>

    <!-- 勤怠テーブル -->
    <table class="attendance-table">
        <thead>
            <tr>
                <th class="date-header">日付</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($attendances as $attendance)
            <tr>
                <td class="date-cell">
                    @php
                    $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
                    $workDate = \Carbon\Carbon::parse($attendance->work_date);
                    @endphp
                    {{ $workDate->format('m/d') }}({{ $weekdays[$workDate->dayOfWeek] }})
                </td>
                <td>{{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}</td>
                <td>{{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}</td>
                <td>
                    @php
                    $totalBreak = $attendance->breakTimes->sum(function ($break) {
                    if ($break->break_start && $break->break_end) {
                    return \Carbon\Carbon::parse($break->break_end)->diffInMinutes(\Carbon\Carbon::parse($break->break_start));
                    }
                    return 0;
                    });
                    echo sprintf('%d:%02d', floor($totalBreak / 60), $totalBreak % 60);
                    @endphp
                </td>
                <td>
                    @php
                    $workTime = '';
                    if ($attendance->clock_in && $attendance->clock_out) {
                    $total = \Carbon\Carbon::parse($attendance->clock_out)->diffInMinutes(\Carbon\Carbon::parse($attendance->clock_in)) - $totalBreak;
                    $workTime = floor($total / 60) . ':' . str_pad($total % 60, 2, '0', STR_PAD_LEFT);
                    }
                    echo $workTime;
                    @endphp
                </td>
                <td>
                    <a href="{{ route('admin.attendance.detail', ['id' => $attendance->id]) }}" class="detail-link">詳細</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6">該当する勤怠データはありません。</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- 月選択モーダル -->
<div id="monthModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close" style="cursor: pointer;">×</span>
        <ul class="month-list">
            @php
            $base = \Carbon\Carbon::parse($yearMonth)->startOfMonth(); // 現在の月
            $startMonth = $base->copy()->subMonths(11); // 過去11カ月分を開始点に
            @endphp

            @for ($i = 0; $i < 12; $i++)
                @php
                $targetMonth=$startMonth->copy()->addMonths($i);
                $ym = $targetMonth->format('Y-m');
                @endphp
                <li>
                    <a href="{{ route('admin.attendance.staff', ['id' => $user->id, 'month' => $ym]) }}">
                        {{ $targetMonth->format('Y年m月') }}
                    </a>
                </li>
                @endfor
        </ul>
    </div>
</div>

<script>
    function toggleMonthModal() {
        const modal = document.getElementById('monthModal');
        modal.style.display = modal.style.display === 'flex' ? 'none' : 'flex';
    }

    document.addEventListener('DOMContentLoaded', function() {
        const closeBtn = document.querySelector('#monthModal .close');
        const modal = document.getElementById('monthModal');

        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                modal.style.display = 'none';
            });
        }

        // 背景クリックで閉じる
        window.addEventListener('click', function(event) {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });
    });
    document.addEventListener('DOMContentLoaded', function() {
        const icon = document.getElementById('calendar-icon');
        const modal = document.getElementById('monthModal');
        const closeBtn = modal.querySelector('.close');

        icon.addEventListener('click', function() {
            modal.style.display = 'flex';
        });

        closeBtn.addEventListener('click', function() {
            modal.style.display = 'none';
        });

        window.addEventListener('click', function(event) {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });
    });
</script>
@endsection
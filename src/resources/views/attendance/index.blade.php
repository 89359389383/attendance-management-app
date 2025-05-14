@extends('layouts.app')

@section('title', 'COACHTECH勤怠管理 - 勤怠一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance/index.css') }}">
@endsection

@section('content')

<body>
    <div class="container">
        <h1 class="title">勤怠一覧</h1>

        <!-- 月切り替えナビゲーション -->
        <div class="month-selector">
            <div class="month-nav">
                <a href="{{ route('attendance.list', ['month' => $currentMonth->copy()->subMonth()->format('Y-m')]) }}" class="month-link">
                    <span class="arrow">←</span> 前月
                </a>
            </div>
            <div class="month-display" onclick="toggleMonthModal()" style="cursor:pointer;">
                <span class="calendar-icon">📅</span>
                <span>{{ $currentMonth->format('Y年m月') }}</span>
            </div>
            <div class="month-nav">
                <a href="{{ route('attendance.list', ['month' => $currentMonth->copy()->addMonth()->format('Y-m')]) }}" class="month-link">
                    翌月 <span class="arrow">→</span>
                </a>
            </div>
        </div>

        <!-- 月選択モーダル -->
        <div id="monthModal" class="modal" style="display: none;">
            <div class="modal-content">
                <!-- ×ボタン -->
                <span class="close" style="cursor: pointer;">×</span>
                <ul class="month-list">
                    @php
                    $startMonth = $currentMonth->copy()->subMonths(11);
                    for ($i = 0; $i < 12; $i++) {
                        $month=$startMonth->copy()->addMonths($i);
                        echo '<a href="' . route('attendance.list', ['month' => $month->format('Y-m')]) . '" style="display:block; text-align:center; margin:5px 0;">' . $month->format('Y年m月') . '</a>';
                        }
                        @endphp
                </ul>
            </div>
        </div>

        <!-- 勤務集計情報 -->
        <div class="summary-box">
            <div class="summary-item">
                <span class="summary-label">勤務日数：</span>
                <span class="summary-value">{{ $attendances->whereNotNull('clock_in')->count() }} 日</span>
            </div>
            <div class="summary-item">
                <span class="summary-label">合計勤務時間：</span>
                <span class="summary-value">
                    @php
                    $totalWorkSeconds = 0;
                    foreach ($attendances as $attendance) {
                    if ($attendance->clock_in && $attendance->clock_out) {
                    $breakSeconds = $attendance->breakTimes->sum(function ($break) {
                    if ($break->break_start && $break->break_end) {
                    return \Carbon\Carbon::parse($break->break_end)->diffInSeconds($break->break_start);
                    }
                    return 0;
                    });
                    $workSeconds = \Carbon\Carbon::parse($attendance->clock_out)->diffInSeconds($attendance->clock_in) - $breakSeconds;
                    $totalWorkSeconds += max(0, $workSeconds);
                    }
                    }

                    // 修正: 四捨五入で分数を表示
                    $roundedTotalMinutes = round($totalWorkSeconds / 60); // 修正: 秒 → 分に四捨五入
                    $hours = floor($roundedTotalMinutes / 60); // 修正: 時間を算出
                    $minutes = $roundedTotalMinutes % 60; // 修正: 残りの分を算出

                    echo $hours . '時間' . str_pad($minutes, 2, '0', STR_PAD_LEFT) . '分'; // 修正: 表示形式
                    @endphp
                </span>
            </div>
        </div>

        <!-- 勤怠情報テーブル -->
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
                        $totalBreakSeconds = $attendance->breakTimes->sum(function ($break) {
                        if ($break->break_start && $break->break_end) {
                        $start = \Carbon\Carbon::parse($break->break_start);
                        $end = \Carbon\Carbon::parse($break->break_end);
                        return $end->diffInSeconds($start);
                        }
                        return 0;
                        });
                        @endphp
                        {{ sprintf('%d:%02d', floor($totalBreakSeconds / 3600), floor(($totalBreakSeconds % 3600) / 60)) }}
                    </td>
                    <td>
                        @php
                        $workTime = '';
                        if ($attendance->clock_in && $attendance->clock_out) {
                        $workSeconds = \Carbon\Carbon::parse($attendance->clock_out)->diffInSeconds(\Carbon\Carbon::parse($attendance->clock_in)) - $totalBreakSeconds;
                        $workSeconds = max(0, $workSeconds);
                        $workTime = floor($workSeconds / 3600) . ':' . str_pad(floor(($workSeconds % 3600) / 60), 2, '0', STR_PAD_LEFT);
                        }
                        echo $workTime;
                        @endphp
                    </td>
                    <td>
                        <a href="{{ url('/attendance/' . $attendance->id) }}" class="detail-link">詳細</a>
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
</body>

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
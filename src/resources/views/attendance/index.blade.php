@extends('layouts.app')

@section('title', 'COACHTECH - 勤怠管理システム')

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
                <!-- 前月に遷移 -->
                <a href="{{ route('attendance.list', ['month' => $currentMonth->copy()->subMonth()->format('Y-m')]) }}" class="month-link">
                    <span class="arrow">←</span> 前月
                </a>
            </div>
            <div class="month-display" onclick="toggleMonthModal()" style="cursor:pointer;">
                <span class="calendar-icon">📅</span>
                <!-- 現在の月 -->
                <span>{{ $currentMonth->format('Y年m月') }}</span>
            </div>
            <div class="month-nav">
                <!-- 翌月に遷移 -->
                <a href="{{ route('attendance.list', ['month' => $currentMonth->copy()->addMonth()->format('Y-m')]) }}" class="month-link">
                    翌月 <span class="arrow">→</span>
                </a>
            </div>
        </div>

        <!-- 月選択モーダル -->
        <div id="monthModal" style="display:none; position:absolute; background:#fff; border:1px solid #ccc; padding:10px; z-index:999;">
            @php
            // 現在表示している月を基準に次の半年と前の半年の合計13ヶ月を計算
            $now = \Carbon\Carbon::now();
            $startMonth = $currentMonth->copy()->subMonths(6); // 6ヶ月前
            $endMonth = $currentMonth->copy()->addMonths(6); // 6ヶ月後

            // 13ヶ月分を表示するためのリンク
            for ($i = 0; $i < 13; $i++) {
                $month=$startMonth->copy()->addMonths($i); // 開始月から順番に1ヶ月ずつ進めていく
                echo '<a href="' . route('attendance.list', ['month' => $month->format('Y-m')]) . '" style="display:block; text-align:center; margin:5px 0;">' . $month->format('Y年m月') . '</a>';
                }
                @endphp
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
                    $totalWorkMinutes = 0;
                    foreach ($attendances as $attendance) {
                    if ($attendance->clock_in && $attendance->clock_out) {
                    $breakMinutes = $attendance->breakTimes->sum(function ($break) {
                    if ($break->break_start && $break->break_end) {
                    return \Carbon\Carbon::parse($break->break_end)->diffInMinutes($break->break_start);
                    }
                    return 0;
                    });
                    $total = \Carbon\Carbon::parse($attendance->clock_out)->diffInMinutes($attendance->clock_in) - $breakMinutes;
                    $totalWorkMinutes += max(0, $total); // マイナス防止
                    }
                    }
                    echo floor($totalWorkMinutes / 60) . '時間' . str_pad($totalWorkMinutes % 60, 2, '0', STR_PAD_LEFT) . '分';
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
                        {{-- 合計休憩時間を算出 --}}
                        @php
                        $totalBreak = $attendance->breakTimes->sum(function ($break) {
                        if ($break->break_start && $break->break_end) {
                        $start = \Carbon\Carbon::parse($break->break_start);
                        $end = \Carbon\Carbon::parse($break->break_end);
                        return $end->diffInMinutes($start);
                        }
                        return 0;
                        });
                        @endphp
                        {{ sprintf('%d:%02d', floor($totalBreak / 60), $totalBreak % 60) }}
                    </td>

                    <td>
                        {{-- 勤務時間合計 --}}
                        @php
                        $workTime = ''; // 初期値は空文字（表示なし）

                        // 出勤時刻と退勤時刻が両方とも存在する場合にのみ計算を行う
                        if ($attendance->clock_in && $attendance->clock_out) {
                        // 出勤と退勤の差分（分単位）から、合計休憩時間を引いて実働時間を計算する
                        $total = \Carbon\Carbon::parse($attendance->clock_out)->diffInMinutes($attendance->clock_in) - $totalBreak;

                        // 実働時間を "時間:分" の形式で表示する（例: 8:15）
                        $workTime = floor($total / 60) . ':' . str_pad($total % 60, 2, '0', STR_PAD_LEFT);
                        }

                        // 計算された勤務時間（または空白）を出力する
                        echo $workTime;
                        @endphp
                    </td>
                    <td>
                        <a href="{{ url('/attendance/' . $attendance->id) }}" class="detail-link">
                            詳細
                        </a>
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
        modal.style.display = modal.style.display === 'block' ? 'none' : 'block';
    }
</script>

@endsection
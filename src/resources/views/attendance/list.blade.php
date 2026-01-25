<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>スタッフ-勤怠一覧画面</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.staffattendancelist.css')}}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
</head>

<body>

    <header class="header-shadow header">
        <div class="header-content">

            <div class="logo">
                <img src="{{ asset('storage/img/coachtechlogo.png') }}" alt="logoアイコン" class="icon logo-icon-img">
            </div>

            <nav class="nav-menu">

                <a href="/attendance" class="nav-button menu-button">
                    <span class="nav-text">勤怠</span>
                </a>

                <a href="/attendance/list" class="nav-button menu-button">
                    <span class="nav-text">勤怠一覧</span>
                </a>

                <a href="{{ route('request.list') }}" class="nav-button menu-button">
                    <span class="nav-text">申請</span>
                </a>

                <form method="POST" action="/logout">
                    @csrf
                    <button type="submit" class="nav-button logout-button">
                        <span class="nav-text">ログアウト</span>
                    </button>
                </form>

            </nav>

        </div>
    </header>

    <main class="list-container">
        <div class="list-form-container">
            <h1 class="page-title">勤怠一覧</h1>

            {{-- 月選択バー --}}
            <div class="date-pager">
                <a href="/attendance/list?month={{ $prevMonth }}" class="date-pager-btn">
                    <img src="{{ asset('storage/img/arrow.png') }}" alt="矢印マーク" class="arrowleft-icon"> 前月
                </a>
                <div class="date-display">
                    <img src="{{ asset('storage/img/calendarmark.png') }}" alt="カレンダーマーク" class="calendar-icon">
                    {{-- $displayDate に '2023/06' という形式で渡す想定 --}}
                    <span class="current-date">{{ $displayDate }}</span>
                </div>
                <a href="/attendance/list?month={{ $nextMonth }}" class="date-pager-btn">翌月 
                    <img src="{{ asset('storage/img/arrow.png') }}" alt="矢印マーク" class="arrowright-icon">
                </a>
            </div>

            {{-- スタッフ用勤怠一覧テーブル --}}
            <div class="table-wrapper">
                <table class="attendance-table">
                    <thead>
                        <tr>
                            <th>日付</th>
                            <th>出勤</th>
                            <th>退勤</th>
                            <th>休憩</th>
                            <th>合計</th>
                            <th>詳細</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($attendances as $attendance)
                        <tr>
                            {{-- 日付のフォーマットもCarbonで行う --}}
                            <td>{{ Carbon::parse($attendance->date)->format('m/d') }}</td>
                            <td>{{ Carbon::parse($attendance->check_in)->format('H:i') }}</td>
                            <td>{{ $attendance->check_out ? Carbon::parse($attendance->check_out)->format('H:i') : '-' }}</td>

                            {{-- ❗ モデルで定義したアクセサを呼び出す --}}
                            <td class="text-center">{{ $attendance->total_rest_time }}</td>
                            <td class="text-center">{{ $attendance->total_working_time }}</td>

                            {{-- 詳細リンクにはIDを忘れずに --}}
                            <td class="text-center">
                                <a href="/attendance/detail/{{ $attendance->id }}" class="detail-link">詳細</a>
                            </td>
                        </tr>
                        @endforeach

                    </tbody>
                </table>
            </div>
        </div>
    </main>

</body>
</html>
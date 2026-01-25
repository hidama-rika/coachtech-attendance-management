<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理者-勤怠一覧画面</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.list.css')}}">
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

                <a href="/admin/attendance/list" class="nav-button menu-button">
                    <span class="nav-text">勤怠一覧</span>
                </a>

                <a href="/admin/staff/list" class="nav-button menu-button">
                    <span class="nav-text">スタッフ一覧</span>
                </a>

                <a href="{{ route('request.list') }}" class="nav-button menu-button">
                    <span class="nav-text">申請一覧</span>
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
            {{-- 1. タイトルを動的に変更（例：2023年06月01日の勤怠） --}}
            <h1 class="page-title">{{ Carbon::parse($displayDate)->isoFormat('YYYY年MM月DD日') }}の勤怠</h1>

            {{-- 日付選択バー --}}
            <div class="date-pager">
                {{-- 2. 前日へのリンク --}}
                <a href="{{ route('admin.attendance.list', ['date' => $prevDate]) }}" class="date-pager-btn">
                    <img src="{{ asset('storage/img/arrow.png') }}" alt="矢印マーク" class="arrowleft-icon"> 前日
                </a>

                <div class="date-display">
                    <img src="{{ asset('storage/img/calendarmark.png') }}" alt="カレンダーマーク" class="calendar-icon">
                    {{-- 3. 中央の表示（例：2023/06/01） --}}
                    <span class="current-date">{{ $displayDate }}</span>
                </div>

                {{-- 4. 翌日へのリンク --}}
                <a href="{{ route('admin.attendance.list', ['date' => $nextDate]) }}" class="date-pager-btn">翌日 
                    <img src="{{ asset('storage/img/arrow.png') }}" alt="矢印マーク" class="arrowright-icon">
                </a>
            </div>

            {{-- 勤怠一覧テーブル --}}
            <div class="table-wrapper">
                <table class="attendance-table">
                    <thead>
                        <tr>
                            <th>名前</th>
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
                            {{-- $attendance->user でUserモデルのデータにアクセス --}}
                            <td>{{ $attendance->user->name }}</td>
                            <td>{{ Carbon::parse($attendance->check_in)->format('H:i') }}</td>
                            <td>{{ $attendance->check_out ? Carbon::parse($attendance->check_out)->format('H:i') : '-' }}</td>

                            {{-- Attendanceモデルで作ったアクセサがここでも使えます --}}
                            <td>{{ $attendance->total_rest_time }}</td>
                            <td>{{ $attendance->total_working_time }}</td>

                            <td>
                                <a href="{{ route('attendance.detail', ['id' => $attendance->id]) }}">詳細</a>
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
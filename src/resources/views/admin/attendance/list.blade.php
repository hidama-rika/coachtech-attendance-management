<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理者-勤怠一覧画面</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.list.css')}}">
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

                <a href="/admin/stamp_correction_request/list" class="nav-button menu-button">
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
            <h1 class="page-title">2023年6月1日の勤怠</h1>

            {{-- 日付選択バー --}}
            <div class="date-pager">
                <a href="#" class="date-pager-btn">
                    <img src="{{ asset('storage/img/arrow.png') }}" alt="矢印マーク" class="arrowleft-icon"> 前日
                </a>
                <div class="date-display">
                    <img src="{{ asset('storage/img/calendarmark.png') }}" alt="カレンダーマーク" class="calendar-icon">
                    <span class="current-date">2023/06/01</span>
                </div>
                <a href="#" class="date-pager-btn">翌日 
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
                        {{-- 以下、ダミーデータ（ループで出力する想定） --}}
                        <tr>
                            <td class="text-center">山田 太郎</td>
                            <td class="text-center">09:00</td>
                            <td class="text-center">18:00</td>
                            <td class="text-center">1:00</td>
                            <td class="text-center">8:00</td>
                            <td class="text-center"><a href="/admin/attendance/1" class="detail-link">詳細</a></td>
                        </tr>
                        {{-- 繰り返し分... --}}
                        @foreach(range(1, 5) as $i)
                        <tr>
                            <td class="text-center">スタッフ名</td>
                            <td class="text-center">09:00</td>
                            <td class="text-center">18:00</td>
                            <td class="text-center">1:00</td>
                            <td class="text-center">8:00</td>
                            <td class="text-center"><a href="#" class="detail-link">詳細</a></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </main>

</body>
</html>
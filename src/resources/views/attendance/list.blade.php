<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>スタッフ-勤怠一覧画面</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.staffattendancelist.css')}}">
</head>

<body>

    <header class="header-shadow header">
        <div class="header-content">

            <div class="logo">
                <img src="{{ asset('storage/img/coachtechlogo.png') }}" alt="logoアイコン" class="icon logo-icon-img">
            </div>

            <nav class="nav-menu">

                <a href="/mypage" class="nav-button menu-button">
                    <span class="nav-text">勤怠</span>
                </a>

                <a href="/mypage" class="nav-button menu-button">
                    <span class="nav-text">勤怠一覧</span>
                </a>

                <a href="/sell" class="nav-button menu-button">
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
                <a href="#" class="date-pager-btn">
                    <img src="{{ asset('storage/img/arrow.png') }}" alt="矢印マーク" class="arrowleft-icon"> 前月
                </a>
                <div class="date-display">
                    <img src="{{ asset('storage/img/calendarmark.png') }}" alt="カレンダーマーク" class="calendar-icon">
                    <span class="current-date">2023/06</span>
                </div>
                <a href="#" class="date-pager-btn">翌月 
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
                        {{-- 以下、ダミーデータ（ループで出力する想定） --}}
                        <tr>
                            <td class="text-center">06/01(木)</td>
                            <td class="text-center">09:00</td>
                            <td class="text-center">18:00</td>
                            <td class="text-center">1:00</td>
                            <td class="text-center">8:00</td>
                            <td class="text-center"><a href="/admin/attendance/1" class="detail-link">詳細</a></td>
                        </tr>
                        {{-- 繰り返し分... --}}
                        @foreach(range(1, 5) as $i)
                        <tr>
                            <td class="text-center">日付</td>
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
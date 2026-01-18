<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理者-修正申請承認画面</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.requestapprove.css')}}">
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

    <main class="container">
        <div class="form-container">
            <h1 class="page-title">勤怠詳細</h1>

            {{-- 勤怠詳細テーブル --}}
            <form action="#" method="POST" class="attendance-form">
                @csrf
                {{-- 名前 --}}
                <div class="form-group">
                    <label class="form-label">名前</label>
                    <div class="form-text name-display">
                        <span>西</span>
                        <span>伶奈</span>
                    </div>
                </div>

                {{-- 日付 --}}
                <div class="form-group">
                    <label class="form-label">日付</label>
                    <div class="form-row-date">
                        <span class="date-unit">2023年</span>
                        <span class="date-unit">6月1日</span>
                    </div>
                </div>

                {{-- 出勤・退勤 --}}
                <div class="form-group">
                    <label class="form-label">出勤・退勤</label>
                    <div class="form-row-text">
                        <span class="text-value">09:00</span>
                        <span class="range-separator">～</span>
                        <span class="text-value">18:00</span>
                    </div>
                </div>

                {{-- 休憩 --}}
                <div class="form-group">
                    <label class="form-label">休憩</label>
                    <div class="form-row-text">
                        <span class="text-value">12:00</span>
                        <span class="range-separator">～</span>
                        <span class="text-value">13:00</span>
                    </div>
                </div>

                {{-- 休憩2（追加用） --}}
                <div class="form-group">
                    <label class="form-label">休憩2</label>
                    <div class="form-row-text">
                    <span class="text-value"></span> {{-- 空データの場合 --}}
                </div>
                </div>

                {{-- 備考 --}}
                <div class="form-group">
                    <label class="form-label">電車遅延のため</label>
                    <div class="form-text-remark">電車遅延のため</div>
                </div>

                {{-- 承認ボタン --}}
                <div class="form-button-area">
                    <button type="submit" class="submit-button">承認</button>
                </div>
            </form>
        </div>
    </main>

</body>
</html>
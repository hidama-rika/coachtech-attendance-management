<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>スタッフ-勤怠登録画面</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.detail.css')}}">
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

    <main class="stamp-container">
        <div class="stamp-wrapper">
            {{-- ステータスバッジ --}}
            <div class="status-badge">
                @if($status == 'out') 勤務外 @elseif($status == 'working') 出勤中 @elseif($status == 'resting') 休憩中 @else 退勤済 @endif
            </div>

            {{-- 日時表示 --}}
            <div class="date-display">2023年6月1日(木)</div>
            <div class="time-display">08:00</div>

            {{-- 打刻アクションエリア --}}
            <div class="stamp-actions">
                @if($status == 'out')
                    {{-- 出勤前：出勤ボタン一つ --}}
                    <form action="/attendance/start" method="POST">
                        @csrf
                        <button type="submit" class="stamp-btn primary">出勤</button>
                    </form>

                @elseif($status == 'working')
                    {{-- 出勤中：退勤と休憩入が横並び --}}
                    <div class="btn-group">
                        <form action="/attendance/end" method="POST">
                            @csrf
                            <button type="submit" class="stamp-btn primary">退勤</button>
                        </form>
                        <form action="/attendance/rest-start" method="POST">
                            @csrf
                            <button type="submit" class="stamp-btn secondary">休憩入</button>
                        </form>
                    </div>

                @elseif($status == 'resting')
                    {{-- 休憩中：休憩戻ボタン一つ --}}
                    <form action="/attendance/rest-end" method="POST">
                        @csrf
                        <button type="submit" class="stamp-btn secondary">休憩戻</button>
                    </form>

                @elseif($status == 'finished')
                    {{-- 退勤後：お疲れ様表示 --}}
                    <p class="finish-message">お疲れ様でした！</p>
                @endif
            </div>
        </div>
    </main>

</body>
</html>
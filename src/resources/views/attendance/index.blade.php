<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>スタッフ-勤怠登録画面</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/index.css')}}">
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

    <main class="stamp-container">
        <div class="stamp-wrapper">

            {{-- ステータスバッジ --}}
            <div class="status-badge">
                @if($status == 'out') 勤務外 @elseif($status == 'working') 出勤中 @elseif($status == 'resting') 休憩中 @else 退勤済 @endif
            </div>

            {{-- 日時表示：Carbonで現在の日付を日本語形式で表示 --}}
            <div class="date-display">
                {{ \Carbon\Carbon::now()->isoFormat('YYYY年M月D日(ddd)') }}
            </div>
            {{-- 時刻表示：初期値を表示し、JSでリアルタイム更新 --}}
            <div class="time-display" id="current-time">
                {{ \Carbon\Carbon::now()->format('H:i') }}
            </div>

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

    {{-- 時刻を1秒ごとに更新するJavaScript --}}
    <script>
        function updateTime() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            document.getElementById('current-time').textContent = `${hours}:${minutes}`;
        }
        setInterval(updateTime, 1000);
    </script>

</body>
</html>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理者-修正申請承認画面</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.requestapprove.css')}}">
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

    <main class="container">
        <div class="form-container">
            <h1 class="page-title">勤怠詳細</h1>

            {{-- 勤怠詳細テーブル --}}
            {{-- 承認処理（RequestController の approve メソッドへ送信） --}}
            <form action="{{ route('admin.request.approve', ['id' => $request->id]) }}" method="POST" class="attendance-form">
                @csrf
                {{-- 名前 --}}
                <div class="form-group">
                    <label class="form-label">名前</label>
                    <div class="form-text name-display">
                        {{-- ユーザー名を名字と名前に分ける場合は、スペース等で調整 --}}
                        <span>{{ $request->user->name }}</span>
                    </div>
                </div>

                {{-- 日付 --}}
                <div class="form-group">
                    <label class="form-label">日付</label>
                    <div class="form-row-date">
                        <span class="date-unit">{{ \Carbon\Carbon::parse($request->attendance->date)->format('Y年') }}</span>
                        <span class="date-unit">{{ \Carbon\Carbon::parse($request->attendance->date)->format('n月j日') }}</span>
                </div>
                </div>

                {{-- 出勤・退勤（修正申請詳細テーブルから取得） --}}
                <div class="form-group">
                    <label class="form-label">出勤・退勤</label>
                    <div class="form-row-text">
                        <span class="text-value">{{ \Carbon\Carbon::parse($request->correctionAttendanceDetail->check_in)->format('H:i') }}</span>
                        <span class="range-separator">～</span>
                        <span class="text-value">{{ \Carbon\Carbon::parse($request->correctionAttendanceDetail->check_out)->format('H:i') }}</span>
                    </div>
                </div>

                {{-- 休憩（修正申請休憩詳細テーブルをループで表示） --}}
                @foreach($request->restDetails as $index => $rest)
                <div class="form-group">
                    <label class="form-label">休憩{{ $index > 0 ? $index + 1 : '' }}</label>
                    <div class="form-row-text">
                        <span class="text-value">{{ \Carbon\Carbon::parse($rest->start_time)->format('H:i') }}</span>
                        <span class="range-separator">～</span>
                        <span class="text-value">{{ \Carbon\Carbon::parse($rest->end_time)->format('H:i') }}</span>
                    </div>
                </div>
                @endforeach

                {{-- 備考 --}}
                <div class="form-group">
                    <label class="form-label">備考</label>
                    <div class="form-text-remark">
                        {{ $request->correctionAttendanceDetail->remark }}
                    </div>
                </div>

                {{-- 承認ボタン --}}
                <div class="form-button-area">
                    {{-- ステータスが pending の場合のみボタンを表示 --}}
                    {{-- 未承認：通常の承認ボタン --}}
                    @if($request->status == 'pending')
                        <button type="submit" class="submit-button">承認</button>
                    @else
                        {{-- 承認済み：CSSクラス .approved-button を適用 --}}
                        <div class="approved-button">承認済み</div>
                    @endif
                </div>
            </form>
        </div>
    </main>

</body>
</html>
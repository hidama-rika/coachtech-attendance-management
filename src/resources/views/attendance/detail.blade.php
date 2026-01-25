<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>スタッフ-勤怠詳細画面</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.detail.css')}}">
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

                <a href="/stamp_correction_request/list" class="nav-button menu-button">
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

    <main class="container">
        <div class="form-container">
            <h1 class="page-title">勤怠詳細</h1>

            {{-- 勤怠詳細テーブル --}}
            <form action="{{ route('attendance.update', ['id' => $attendance->id]) }}" method="POST" class="attendance-form">
                @csrf

                {{-- バリデーションエラーの表示エリアを追加 (FN030) --}}
                @if ($errors->any())
                    <div class="error-messages">
                        @foreach ($errors->all() as $error)
                            <p class="error-text">{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                {{-- 名前 --}}
                <div class="form-group">
                    <label class="form-label">名前</label>
                    <div class="form-text name-display">
                        <span>{{ $attendance->user->name }}</span>
                    </div>
                </div>

                {{-- 日付 --}}
                <div class="form-group">
                    <label class="form-label">日付</label>
                    <div class="form-row-date">
                        <span class="date-unit">{{ \Carbon\Carbon::parse($attendance->date)->format('Y年') }}</span>
                        <span class="date-unit">{{ \Carbon\Carbon::parse($attendance->date)->format('n月j日') }}</span>
                    </div>
                </div>

                {{-- 出勤・退勤 --}}
                <div class="form-group">
                    <label class="form-label">出勤・退勤</label>
                    <div class="form-row-input">
                        <input type="text" name="check_in" class="input-field" value="{{ old('check_in', substr($attendance->check_in, 0, 5)) }}">
                        <span class="range-separator">～</span>
                        <input type="text" name="check_out" class="input-field" value="{{ old('check_out', substr($attendance->check_out, 0, 5)) }}">
                    </div>
                </div>

                {{-- 休憩：保存されている回数分だけループ表示 (FN021対応) --}}
                @foreach($attendance->rests as $index => $rest)
                <div class="form-group">
                    <label class="form-label">休憩{{ $index + 1 }}</label>
                    <div class="form-row-input">
                        <input type="text" name="rests[{{ $rest->id }}][start]" class="input-field" value="{{ old("rests.{$rest->id}.start", substr($rest->start_time, 0, 5)) }}">
                        <span class="range-separator">～</span>
                        <input type="text" name="rests[{{ $rest->id }}][end]" class="input-field" value="{{ old("rests.{$rest->id}.end", substr($rest->end_time, 0, 5)) }}">
                    </div>
                </div>
                @endforeach

                {{-- 休憩2（追加用） --}}
                {{-- 【A案】Figmaデザイン通りの固定枠（回答次第で削除） --}}
                <div class="form-group">
                    <label class="form-label">休憩2</label>
                    <div class="form-row-input">
                        <input type="text" class="input-field" value="">
                        <span class="range-separator">～</span>
                        <input type="text" class="input-field" value="">
                    </div>
                </div>

                {{-- 2. コーチへの提案通り、常に末尾に1つ「新規入力用」を表示する --}}
                {{-- 【B案】動的に増える新規枠（採用予定のロジック） --}}
                <div class="form-group">
                    <label class="form-label">休憩{{ $attendance->rests->count() + 1 }}</label>
                    <div class="form-row-input">
                        {{-- コントローラーの update メソッドと合わせるため、name属性を new_rest に設定 --}}
                        <input type="text" name="new_rest[start]" class="input-field" value="{{ old('new_rest.start') }}">
                        <span class="range-separator">～</span>
                        <input type="text" name="new_rest[end]" class="input-field" value="{{ old('new_rest.end') }}">
                    </div>
                </div>

                {{-- 備考 --}}
                <div class="form-group">
                    <label class="form-label">備考</label>
                    <textarea name="remark" class="textarea-field">{{ old('remark') }}</textarea>
                </div>

                {{-- 修正ボタン (FN028: 修正申請を出す) --}}
                <div class="form-button-area">
                    <button type="submit" class="submit-button">修正</button>
                </div>
            </form>
        </div>
    </main>

</body>
</html>
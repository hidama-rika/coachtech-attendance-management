<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>スタッフ-勤怠詳細画面</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.detail.css') }}?v={{ time() }}">
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

    <main class="container staff-page"> {{-- 管理者と一般ユーザーで表示切替 --}}
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
                        @php
                            // 承認待ちなら申請データを、そうでなければ元の勤怠データを参照する
                            $displayCheckIn = $isPending ? substr($pendingRequest->correctionAttendanceDetail->check_in, 0, 5) : substr($attendance->check_in, 0, 5);
                            $displayCheckOut = $isPending ? substr($pendingRequest->correctionAttendanceDetail->check_out, 0, 5) : substr($attendance->check_out, 0, 5);
                        @endphp

                        <input type="text" name="check_in" class="input-field @if($isPending) is-pending @endif" value="{{ old('check_in', $displayCheckIn) }}" @if($isPending) readonly @else onfocus="(this.type='time')" onblur="if(!this.value)this.type='text'" @endif>
                        <span class="range-separator">～</span>
                        <input type="text" name="check_out" class="input-field @if($isPending) is-pending @endif" value="{{ old('check_out', $displayCheckOut) }}" @if($isPending) readonly @else onfocus="(this.type='time')" onblur="if(!this.value)this.type='text'" @endif>
                    </div>
                </div>

                {{-- 休憩：保存されている回数分だけループ表示 (FN021対応) --}}
                @foreach($attendance->rests as $index => $rest)
                <div class="form-group">
                    <label class="form-label">休憩{{ $index + 1 }}</label>
                    <div class="form-row-input">
                        @php
                            // 承認待ちなら申請詳細(restDetails)からこの休憩IDに一致する値を探す
                            $pendingRest = $isPending ? $pendingRequest->restDetails->where('rest_id', $rest->id)->first() : null;
                            $displayStart = $pendingRest ? substr($pendingRest->start_time, 0, 5) : substr($rest->start_time, 0, 5);
                            $displayEnd = $pendingRest ? substr($pendingRest->end_time, 0, 5) : substr($rest->end_time, 0, 5);
                        @endphp

                        <input type="text" name="rests[{{ $rest->id }}][start_time]" class="input-field @if($isPending) is-pending @endif" value="{{ old("rests.{$rest->id}.start", $displayStart) }}" @if($isPending) readonly @else onfocus="(this.type='time')" onblur="if(!this.value)this.type='text'" @endif>
                        <span class="range-separator">～</span>
                        <input type="text" name="rests[{{ $rest->id }}][end_time]" class="input-field @if($isPending) is-pending @endif" value="{{ old("rests.{$rest->id}.end", $displayEnd) }}" @if($isPending) readonly @else onfocus="(this.type='time')" onblur="if(!this.value)this.type='text'" @endif>
                    </div>
                </div>
                @endforeach

                {{-- 2. 動的に増える新規枠。常に末尾に1つ「新規入力用」を表示する --}}
                <div class="form-group">
                    <label class="form-label">休憩{{ $attendance->rests->count() + 1 }}</label>
                    <div class="form-row-input">
                        {{-- コントローラーの update メソッドと合わせるため、name属性を new_rest に設定 --}}
                        <input type="text" name="new_rest[start]" class="input-field @if($isPending) is-pending @endif" value="{{ old('new_rest.start') }}" @if($isPending) readonly @else onfocus="(this.type='time')" onblur="if(!this.value)this.type='text'" @endif>
                        <span class="range-separator">～</span>
                        <input type="text" name="new_rest[end]" class="input-field @if($isPending) is-pending @endif" value="{{ old('new_rest.end') }}" @if($isPending) readonly @else onfocus="(this.type='time')" onblur="if(!this.value)this.type='text'" @endif>
                    </div>
                </div>

                {{-- 備考 --}}
                <div class="form-group">
                    <label class="form-label">備考</label>
                    @php
                        // 承認待ちなら申請詳細テーブルのremark、そうでなければ現在の勤怠のremarkを表示
                        $displayRemark = $isPending ? $pendingRequest->correctionAttendanceDetail->remark : $attendance->remark;
                    @endphp
                    <textarea name="remark" class="textarea-field @if($isPending) is-pending @endif" @if($isPending) readonly @endif>{{ old('remark', $displayRemark) }}</textarea>
                </div>

                {{-- 修正ボタン (FN028: 修正申請を出す) --}}
                <div class="form-button-area">
                    @if($isPending)
                        {{-- 承認待ちの場合：メッセージのみ表示 (FN033) --}}
                        <p class="pending-message">
                            ＊承認待ちのため修正はできません。
                        </p>
                    @else
                        {{-- 通常時：修正ボタンを表示 --}}
                        <button type="submit" class="submit-button">修正</button>
                    @endif
                </div>
            </form>
        </div>
    </main>

</body>
</html>
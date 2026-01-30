<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理者-申請一覧画面</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.requestlist.css')}}">
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
            <h1 class="page-title">申請一覧</h1>

            {{-- 承認状態タブ --}}
            <div class="status-tabs">
                {{-- クエリパラメータ 'tab' が 'approved' でなければ『承認待ち』をアクティブにする --}}
                <a href="{{ route('admin.request.list', ['tab' => 'pending']) }}" class="tab-item {{ request('tab') != 'approved' ? 'active' : '' }}">承認待ち</a>

                <a href="{{ route('admin.request.list', ['tab' => 'approved']) }}" class="tab-item {{ request('tab') == 'approved' ? 'active' : '' }}">承認済み</a>
            </div>

            {{-- 申請一覧テーブル --}}
            <div class="table-wrapper">
                <table class="attendance-table">
                    <thead>
                        <tr>
                            <th>状態</th>
                            <th>名前</th>
                            <th>対象日時</th>
                            <th>申請理由</th>
                            <th>申請日時</th>
                            <th>詳細</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- 表示するデータをタブの状態によって切り替える --}}
                        @php
                            $displayRequests = request('tab') == 'approved' ? $approvedRequests : $pendingRequests;
                        @endphp

                        @forelse($displayRequests as $request)
                        <tr>
                            <td class="text-center">
                                {{ $request->status == 'pending' ? '承認待ち' : '承認済み' }}
                            </td>
                            <td class="text-center">{{ $request->user->name }}</td>
                            <td class="text-center">
                                {{ \Carbon\Carbon::parse($request->attendance->date)->format('Y/m/d') }}
                            </td>
                            <td class="text-center">
                                {{-- 修正申請時の備考（理由）を表示 --}}
                                {{ $request->correctionAttendanceDetail->remark ?? '---' }}
                            </td>
                            <td class="text-center">
                                {{ $request->created_at->format('Y/m/d') }}
                            </td>
                            <td class="text-center">
                                {{-- 管理者用の詳細承認画面へリンク --}}
                                <a href="{{ route('admin.request.show', ['id' => $request->id]) }}" class="detail-link">詳細</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center" style="padding: 20px;">
                                {{ request('tab') == 'approved' ? '承認済みの申請はありません' : '承認待ちの申請はありません' }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>

</body>
</html>
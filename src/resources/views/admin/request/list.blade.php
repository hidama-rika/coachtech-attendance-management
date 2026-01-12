<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理者-申請一覧画面</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.requestlist.css')}}">
</head>

<body>

    <header class="header-shadow header">
        <div class="header-content">

            <div class="logo">
                <img src="{{ asset('storage/img/coachtechlogo.png') }}" alt="logoアイコン" class="icon logo-icon-img">
            </div>

            <nav class="nav-menu">

                <a href="/mypage" class="nav-button menu-button">
                    <span class="nav-text">勤怠一覧</span>
                </a>

                <a href="/mypage" class="nav-button menu-button">
                    <span class="nav-text">スタッフ一覧</span>
                </a>

                <a href="/sell" class="nav-button menu-button">
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
                {{-- 現在の選択状態に合わせて active クラスを切り替える想定です --}}
                <a href="#" class="tab-item active">承認待ち</a>
                <a href="#" class="tab-item">承認済み</a>
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
                        {{-- 以下、ダミーデータ（ループで出力する想定） --}}
                        <tr>
                            <td class="text-center">承認待ち</td>
                            <td class="text-center">西玲奈</td>
                            <td class="text-center">2023/06/01</td>
                            <td class="text-center">遅延のため</td>
                            <td class="text-center">2023/06/02</td>
                            <td class="text-center"><a href="/admin/attendance/1" class="detail-link">詳細</a></td>
                        </tr>
                        {{-- 繰り返し分... --}}
                        @foreach(range(1, 5) as $i)
                        <tr>
                            <td class="text-center">承認待ち</td>
                            <td class="text-center">スタッフ</td>
                            <td class="text-center">2023/06/01</td>
                            <td class="text-center">遅延のため</td>
                            <td class="text-center">2023/06/02</td>
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
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理者-スタッフ一覧画面</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.stafflist.css')}}">
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
            <h1 class="page-title">スタッフ一覧</h1>

            {{-- スタッフ一覧テーブル --}}
            <div class="table-wrapper">
                <table class="attendance-table">
                    <thead>
                        <tr>
                            <th>名前</th>
                            <th>メールアドレス</th>
                            <th>月次勤怠</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- 以下、ダミーデータ（ループで出力する想定） --}}
                        <tr>
                            <td class="text-center">山田 太郎</td>
                            <td class="text-center">taro.y@coachtech.com</td>
                            <td class="text-center"><a href="/admin/attendance/1" class="detail-link">詳細</a></td>
                        </tr>
                        {{-- 繰り返し分... --}}
                        @foreach(range(1, 5) as $i)
                        <tr>
                            <td class="text-center">スタッフ名</td>
                            <td class="text-center">staff@coachtech.com</td>
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
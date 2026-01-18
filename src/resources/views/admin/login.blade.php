<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理者-ログイン</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <!-- <link rel="stylesheet" href="{{ asset('css/admin.login.css')}}"> -->
    <link rel="stylesheet" href="http://localhost/css/admin.login.css">
</head>

<body>

    <header class="header-shadow header">
        <div class="header-content">

            <div class="logo">
                <img src="{{ asset('storage/img/coachtechlogo.png') }}" alt="logoアイコン" class="icon logo-icon-img">
            </div>

        </div>
    </header>

	<main class="login-container">
        <div class="login-form-container">

            <div class="form-title">管理者ログイン</div>

            <form class="form" action="/login" method="post" novalidate>
                @csrf

                {{-- メールアドレス --}}
                <div class="form-group">
                    <label for="email">メールアドレス</label>
                    <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required autofocus placeholder="メールアドレスを入力">
                    <p class="login-form__error-message">
                        @error('email')
                        {{ $message }}
                        @enderror
                    </p>
                </div>

                {{-- パスワード --}}
                <div class="form-group">
                    <label for="password">パスワード</label>
                    <input id="password" type="password" class="form-control" name="password" required placeholder="パスワードを入力">
                    <p class="login-form__error-message">
                        @error('password')
                        {{ $message }}
                        @enderror
                    </p>
                </div>

                {{-- ログインボタン --}}
                <button type="submit" class="login-btn">
                    管理者ログインする
                </button>
            </form>

        </div>

    </main>
</body>

</html>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <title>新規登録</title>
    <style>
        /* カラーパレット */
        :root {
            --bg-dark: #27262a;
            --bg-light-gray: #b8bcc3;
            --text-main: #0f131b;
            --accent-color: #d1d1d5;
            --button-hover: #c1c1c4;
        }

        .bgcolor {
            background-color: var(--bg-dark);
            clip-path: polygon(0 0, 100% 0, 100% 50%, 0 100%);
        }
    </style>
</head>

<body class="bg-[var(--bg-light-gray)] relative">
    <div class="absolute w-full h-[60vh] bgcolor -z-10"></div>

    <div class="flex items-center justify-center h-screen">
        <div class="flex shadow-2xl rounded-xl overflow-hidden max-w-5xl w-full">
            <div class="w-1/2 hidden md:block relative">
                <img src="{{ asset('images/car.svg') }}" alt="Background Image" class="w-full h-full object-cover">
                <div class="absolute top-0 left-0 w-full h-full bg-black opacity-40"></div>
                <h2
                    class="absolute top-52 left-[73%]
                    bg-white px-4 py-2 rounded-xl text-2xl font-bold text-[var(--text-main)]">
                    register
                </h2>
                <a href="{{ route('login') }}"
                    class="absolute top-64 left-[75%]
                    px-6 py-2 text-2xl font-bold text-white">
                    Login
                </a>
            </div>
            <div class="w-full md:w-2/3 p-16 flex flex-col justify-center bg-white">
                <h1 class="text-4xl text-center mb-10 mt-10 text-[var(--text-main)]">新規登録</h1>
                <form action="{{ route('register') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="email" class="text-[var(--text-main)] text-lg">メールアドレス</label>
                        <div class="relative">
                            <img src="{{ asset('images/mail_24dp_E8EAED_FILL0_wght400_GRAD0_opsz24.svg') }}"
                                class="absolute top-1/2 left-3 transform -translate-y-[40%] w-6 h-6">
                            <input type="email" name="email" value="{{ old('email') }}"
                                class="w-full px-10 py-3 mt-1 text-lg border {{ $errors->has('email') ? 'border-red-500' : 'border-[var(--bg-dark)]' }} rounded-md focus:outline-none focus:ring-2 focus:ring-[var(--bg-dark)] hover:scale-103 hover:shadow-lg transition-all duration-200" />
                        </div>
                        @error('email')
                            <div class="text-red-600 text-base text-base mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-4 relative">
                        <label for="password" class="text-[var(--text-main)] text-lg">パスワード</label>
                        <div class="relative">
                            <img src="{{ asset('images/lock_24dp_E8EAED_FILL0_wght400_GRAD0_opsz24.svg') }}"
                                class="absolute top-1/2 left-3 transform -translate-y-[40%] w-6 h-6">
                            <input type="password" id="password" name="password" value="{{ old('password') }}"
                                class="w-full px-10 py-3 mt-1 text-lg border {{ $errors->has('password') ? 'border-red-500' : 'border-[var(--bg-dark)]' }} rounded-md focus:outline-none focus:ring-2 focus:ring-[var(--bg-dark)] hover:scale-103 hover:shadow-lg transition-all duration-200" />
                            <button type="button" id="toggle-password"
                                class="absolute top-1/2 right-3 transform -translate-y-1/2">
                                <img id="eye-icon" src="{{ asset('images/eye-slash-regular.svg') }}" alt="eye-icon"
                                    class="w-6 h-6 cursor-pointer">
                            </button>
                        </div>
                        @error('password')
                            <div class="text-red-600 text-base mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="password_confirmation" class="text-[var(--text-main)] text-lg">パスワード（確認用）</label>
                        <div class="relative">
                            <img src="{{ asset('images/lock_24dp_E8EAED_FILL0_wght400_GRAD0_opsz24.svg') }}"
                                class="absolute top-1/2 left-3 transform -translate-y-[40%] w-6 h-6">
                            <input type="password" name="password_confirmation"
                                class="w-full px-10 py-3 mt-1 text-lg border {{ $errors->has('password_confirmation') ? 'border-red-500' : 'border-[var(--bg-dark)]' }} rounded-md focus:outline-none focus:ring-2 focus:ring-[var(--bg-dark)] hover:scale-103 hover:shadow-lg transition-all duration-200" />
                        </div>
                        @error('password_confirmation')
                            <div class="text-red-600 text-base mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit"
                        class="text-[var(--text-main)] bg-[var(--accent-color)]
                            shadow-xl py-3 text-lg w-full mt-10 mb-10 rounded-xl
                            hover:hover:bg-[var(--button-hover)]">
                        登録
                    </button>
                </form>
                <div class="flex justify-center">
                    <a href="{{ route('top.index') }}" class="text-lg text-[var(--text-main)]">ログインなしで使用</a>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.getElementById('toggle-password').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.src = '{{ asset('images/eye-regular.svg') }}';
            } else {
                passwordInput.type = 'password';
                eyeIcon.src = '{{ asset('images/eye-slash-regular.svg') }}';
            }
        });
    </script>
</body>

</html>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <title>プロフィール編集ページ</title>
    <style>
        :root {
            --bg-dark: #27262a;
            --bg-light-gray: #b8bcc3;
            --text-main: #0f131b;
            --white: #ffffff;
            --hover: #a1a5ab;
            --bg-hover: #383b42;
        }
    </style>
</head>
<body class="bg-[var(--bg-dark)] bg-[var(--bg-light-gray)]text-[var(--text-main)] font-sans">

    <!-- メインコンテンツ -->
    <div class="flex justify-center items-center min-h-screen">
        <div class="w-full max-w-lg p-8 bg-[var(--bg-light-gray)] rounded-lg shadow-lg">
            <!-- タイトル -->
            <h1 class="text-2xl font-bold text-center mb-4">プロフィール編集</h1>

            <!-- フォーム -->
            <form action="{{ route('mypage.update') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="flex justify-center">
                    <div class="bg-transparent relative w-32 h-32 border border-dashed border-gray-300 rounded-full">
                        <div id="imagePreview" class="absolute inset-0 hidden items-center justify-center z-10">
                            <img id="previewImage" src="" alt="プレビュー画像" class="w-full h-full object-cover rounded-full shadow-md cursor-pointer">
                        </div>

                        <!-- アイコンボタン -->
                        <label for="image" class="cursor-pointer flex flex-col items-center justify-center w-full h-full rounded-full hover:bg-gray-100 transition z-20">
                            <img src="{{ $user->avatar ? asset('storage/' . $user->avatar) : asset('images/imag.svg') }}" alt="画像アップロード" class="w-12 h-12 opacity-70">
                            <span class="mt-2 text-sm text-gray-600">画像を選択</span>
                            <input type="file" name="avatar" id="image" accept="image/*" class="hidden">
                        </label>
                    </div>
                </div>


                <!-- 名前 -->
                <div>
                    <label for="name" class="block font-semibold text-lg mb-2">名前</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}"
                        class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[var(--bg-dark)] @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- 自己紹介 -->
                <div>
                    <label for="text" class="block font-semibold text-lg mb-2">自己紹介</label>
                    <textarea name="text" id="text" rows="4" class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[var(--bg-dark)] @error('text') border-red-500 @enderror">{{ old('text', $user->text) }}</textarea>
                    @error('text')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex space-x-4 justify-end">
                    <!-- キャンセルボタン -->
                    <a href="{{ route('mypage.index') }}" class="text-center bg-[var(--bg-dark)] text-[var(--white)] py-2 px-4 rounded-md hover:bg-[var(--bg-hover)] transition duration-300">
                        キャンセル
                    </a>

                    <!-- 更新ボタン -->
                    <button type="submit" class="text-center bg-[var(--bg-dark)] text-[var(--white)] py-2 px-4 rounded-md shadow-md hover:bg-[var(--bg-hover)] transition duration-300">
                        更新する
                    </button>
                </div>
            </form>
        </div>
    </div>
    <script>
        document.getElementById('image').addEventListener('change', function(event) {
            const file = event.target.files[0];  // 画像ファイルを取得

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewImage = document.getElementById('previewImage');  // プレビュー表示エリアを取得
                    previewImage.src = e.target.result;  // プレビュー画像を更新
                    document.getElementById('imagePreview').classList.remove('hidden');  // プレビュー表示エリアを表示
                    document.getElementById('imagePreview').classList.add('flex');  // プレビュー表示エリアを表示
                };
                reader.readAsDataURL(file);  // ファイルを読み込む
            }
        });

        // 画像プレビューをクリックするとファイル選択ダイアログを開く
        document.getElementById('previewImage').addEventListener('click', function() {
            document.getElementById('image').click();  // input要素をクリックしてファイルダイアログを開く
        });

        // ページ読み込み時に既存の画像を表示
        document.addEventListener("DOMContentLoaded", function() {
            const existingImage = "{{ $user->avatar ? asset('storage/' . $user->avatar) : '' }}";
            if (existingImage) {
                const previewImage = document.getElementById('previewImage');
                previewImage.src = existingImage;  // 既存の画像をプレビューに設定
                document.getElementById('imagePreview').classList.remove('hidden');  // プレビュー表示エリアを表示
                document.getElementById('imagePreview').classList.add('flex');  // プレビュー表示エリアを表示
            }
        });
    </script>
</body>
</html>

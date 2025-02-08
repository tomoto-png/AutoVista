<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>プロフィール編集ページ</title>
</head>
<body>
    <a href="{{ route('mypage.index') }}" class=""><</a>
    <h1>プロフィール編集</h1>
    <form action="{{ route('mypage.update') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
        @csrf
        @method('PUT')
        <div>
            <label for="name" class="block font-semibold">名前</label>
            <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}"
                class="w-full p-2 border rounded-md @error('name') border-red-500 @enderror">
            @error('name')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
    
        <!-- 自己紹介 -->
        <div>
            <label for="text" class="block font-semibold">自己紹介</label>
            <textarea name="text" id="text" class="w-full p-2 border rounded-md @error('text') border-red-500 @enderror">{{ old('text', $user->text) }}</textarea>
            @error('text')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
    
        <!-- 画像 -->
        <div>
            <label for="avatar" class="block font-semibold">画像</label>
            <input type="file" name="avatar" id="avatar" class="w-full p-2 border rounded-md @error('avatar') border-red-500 @enderror">
            @error('avatar')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
    
        <!-- 更新ボタン -->
        <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700">
            更新
        </button>
    </form>
</body>
</html>
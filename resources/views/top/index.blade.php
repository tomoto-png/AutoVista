<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ホームページ</title>
    <style>
        :root {
            --bg-dark: #27262a;
            --bg-light-gray: #b8bcc3;
            --text-main: #0f131b;
            --white: #ffffff;
            --hover: #a1a5ab;
            --bg-hover: #383b42;
        }
        h1 {
            text-underline-offset: 10px;
        }
    </style>
</head>
<body class="bg-[var(--bg-dark)] text-[var(--white)]">
    <div class="max-w-5xl mx-auto relative">
        {{-- ヘッダー --}}
        <div class="flex items-center justify-center fixed top-0 left-0 w-full z-50 mt-5">
            @if(request()->filled('query') || request()->filled('price_tag_id'))
                <h1 class="text-3xl font-bold p-4 underline transition-transform duration-300 hover:scale-105 cursor-pointer"
                    onclick="window.scrollTo({ top: 0, behavior: 'smooth' });">
                    検索結果
                </h1>
            @else
                <h1 class="text-3xl font-bold p-4 underline transition-transform duration-300 hover:scale-105 cursor-pointer"
                    onclick="location.reload();">
                    おすすめ
                </h1>
            @endif
            <a href="{{ route('mypage.index') }}"
               class="text-2xl font-bold p-4 opacity-60 hover:scale-105 transition duration-300">
               マイページ
            </a>
        </div>
        {{-- 投稿ボタン --}}
        @auth
            <button id="openModal"
                class="right-6 bottom-6 duration-300 md:right-80 md:bottom-auto md:transform-none border-4 border-[var(--white)] p-3 rounded-xl shadow-lg fixed transition-all z-50 hover:bg-[var(--hover)]">
                <img src="{{ asset('images/add_24dp_E8EAED_FILL0_wght400_GRAD0_opsz24.svg') }}" alt="投稿" class="w-8 h-8">
            </button>
        @endauth
        <div class="mt-40">
            {{-- 検索 --}}
            <form action="{{ route('top.index') }}" method="GET" class="mb-4 relative flex items-center justify-center flex-wrap md:flex-nowrap"  id="searchForm">
                <div class="flex border rounded-lg w-full max-w-2xl relative">
                    <input type="text" id="query" name="query" placeholder="キーワード検索"
                           class="border-none px-3 py-3 text-lg text-[var(--text-main)] focus:outline-none focus:ring-2 focus:ring-blue-500 w-full"
                           value="{{ request('query') }}" autocomplete="off" />

                    <img src="{{ asset('images/close_24dp_E8EAED_FILL0_wght400_GRAD0_opsz24 (1).svg') }}"
                         class="w-8 h-8 absolute top-1/2 right-36 transform -translate-y-1/2 cursor-pointer"
                         id="closeButton">

                    <div class="border-r border-gray-300 h-full"></div>

                    <select id="price_tag_id" name="price_tag_id"
                            class="border-none px-6 py-3 text-lg text-[var(--text-main)] focus:outline-none focus:ring-2 focus:ring-blue-500 max-w-36 truncate"  onchange="document.getElementById('searchForm').submit();">
                        <option value="">価格帯</option>
                        @foreach($priceTags as $priceTag)
                            <option value="{{ $priceTag->id }}" {{ request('price_tag_id') == $priceTag->id ? 'selected' : '' }}>
                                {{ $priceTag->name }}
                            </option>
                        @endforeach
                    </select>

                    <!-- 候補リスト -->
                    <div id="suggestions" class="hidden absolute top-full left-0 bg-white text-[var(--text-main)] w-full max-w-[calc(100%-9rem)] rounded mt-1 shadow-lg z-50 max-h-36 overflow-y-auto text-sm"></div>
                </div>

                <button type="submit"
                    class="bg-[var(--bg-light-gray)] px-6 py-3 rounded-lg hover:bg-[var(--hover)] shadow-md hover:shadow-lg transition-all duration-300 flex items-center justify-center relative w-full md:w-auto md:ml-5 mt-5 md:mt-0">
                    <img src="{{ asset('images/search_24dp_E8EAED_FILL0_wght400_GRAD0_opsz24.svg') }}"
                        class="w-8 h-8 transition-transform duration-300 group-hover:scale-110">
                </button>
            </form>
            @if ($errors->any())
                <script>
                    document.addEventListener("DOMContentLoaded", function() {
                        const postModal = document.getElementById('postModal');
                        postModal.classList.remove('hidden'); // モーダルを表示
                        postModal.classList.add('flex');
                    });
                </script>
            @endif
            {{-- 投稿モーダル --}}
            <div id="postModal" class="hidden fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50">
                <div class="bg-[var(--bg-light-gray)] p-8 rounded-2xl shadow-2xl w-full max-w-3xl text-[var(--text-main)] relative">
                    <h2 class="text-3xl font-bold text-gray-800 mb-6 border-b pb-2">新規投稿</h2>

                    <form id="postForm" enctype="multipart/form-data" method="POST" class="space-y-5">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <div>
                                    <input type="text" name="title" id="title"
                                        class="w-full text-lg border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-400 focus:outline-none"
                                        placeholder="タイトルを入力" required />
                                    @error('title')
                                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div>
                                    <select name="price_tag_id" id="price_tag_id" required
                                        class="w-full text-lg border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-400 focus:outline-none">
                                        <option value="">値段を選択してください</option>
                                        @foreach($priceTags as $priceTag)
                                            <option value="{{ $priceTag->id }}" {{ old('price_tag_id') == $priceTag->id ? 'selected' : '' }}>
                                                {{ $priceTag->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('price_tag_id')
                                        <div class="text-red-500 text-base mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="flex justify-center">
                                    <div class="bg-[var(--white)] relative w-64 h-64">
                                        <div id="imagePreview" class="absolute inset-0 hidden items-center justify-center z-10">
                                            <img id="previewImage" src="" alt="プレビュー画像" class="w-full h-full object-cover rounded-lg shadow-md cursor-pointer">
                                        </div>
                                        <label for="image" class="cursor-pointer flex flex-col items-center justify-center w-full h-full border-2 border-dashed border-gray-300 rounded-lg hover:bg-gray-100 transition z-20">
                                            <img src="{{ asset('images/imag.svg') }}" alt="画像アップロード" class="w-12 h-12 opacity-70">
                                            <span class="mt-2 text-base text-gray-600">画像を選択</span>
                                            <input type="file" name="image" id="image" accept="image/*" class="hidden" required />
                                        </label>
                                        <p class="text-red-600 text-base mt-1">※画像は必須です忘れないように！</p>
                                    </div>
                                </div>
                                @error('image')
                                    <div class="text-red-500 text-base mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="space-y-4">
                                <div class="relative">
                                    <input type="text" id="tagInput" name="tags"
                                        class="w-full text-lg border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-400 focus:outline-none"
                                        placeholder="タグを入力(複数可能)" autocomplete="off">
                                    @error('tags')
                                        <div class="text-red-500 text-base mt-1">{{ $message }}</div>
                                    @enderror
                                    <div id="tagSuggestions"
                                        class="absolute w-full border rounded-lg mt-1 hidden z-10 shadow-lg"></div>
                                </div>
                                <div id="selectedTagsContainer" class="flex flex-wrap gap-2 mt-2"></div>
                            </div>
                        </div>
                        <div class="flex justify-end space-x-3 mt-6">
                            <button type="button" id="closeModal"
                                class="px-4 py-2 rounded-lg text-lg bg-[var(--bg-dark)] text-[var(--white)] hover:bg-[var(--bg-hover)] transition">キャンセル</button>
                            <button type="submit"
                                class="px-6 py-2 text-lg bg-[var(--bg-dark)] text-[var(--white)] rounded-lg hover:bg-[var(--bg-hover)] transition shadow-md">投稿</button>
                        </div>
                    </form>
                </div>
            </div>
            <div id="postList" class="mt-20 columns-1 sm:columns-2 md:columns-3 gap-4 space-y-4">
                @forelse($posts as $post)
                    <div class="break-inside-avoid rounded-lg overflow-hidden shadow-lg post relative group" data-gallery-id="{{ $post->id }}">
                        <img src="{{ asset('storage/' . $post->image_path) }}" alt="画像" class="w-full h-auto rounded-t-lg cursor-pointer image-thumbnail transition-all duration-300 group-hover:brightness-50" data-full="{{ asset('storage/' . $post->image_path) }}">
                        <button class="dots-btn absolute top-2 right-2 text-white bg-black bg-opacity-50 rounded-full p-1 focus:outline-none opacity-0 group-hover:opacity-100 ">
                            <img src="{{ asset('images/more_horiz_24dp_E8EAED_FILL0_wght400_GRAD0_opsz24.svg') }}" class="w-5 h-5">
                        </button>

                        <div class="popover absolute top-10 right-2 bg-white text-sm shadow-lg rounded-lg p-4 w-48 hidden z-10">
                            <p class="font-semibold text-sm text-gray-600 mb-2">{{ $post->title }}</p>
                            <p class="text-gray-600 text-sm mb-2">{{ $post->priceTag->name ?? '値段未設定' }}</p>
                            <p class="text-gray-600 text-sm mb-2">{{ $post->likes_count }} いいね</p>
                            <div class="flex flex-wrap gap-1">
                                @foreach($post->tags as $tag)
                                    <span class="bg-gray-200 text-gray-700 px-2 py-1 rounded-lg text-sm">{{ $tag->name }}</span>
                                @endforeach
                            </div>
                        </div>
                        @auth
                            <button class="like-btn absolute bottom-2 right-2 text-xl opacity-0 group-hover:opacity-100 transition-opacity duration-300" data-gallery-id="{{ $post->id }}">
                                @if(in_array($post->id, $likedGalleries))
                                    ❤️
                                @else
                                    🤍
                                @endif
                            </button>
                        @else
                            <p class="text-sm mt-1 text-[var(--white)]">
                                <a href="{{ route('login') }}" class="underline">ログインでいいね</a>
                            </p>
                        @endauth
                    </div>
                @empty
                    <p class="text-center text-white">投稿がまだありません。</p>
                @endforelse
            </div>
            <!-- モーダル -->
            <div id="modal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
                <div class="modal-content flex justify-center items-center w-full h-full">
                    <img id="modal-image" src="" alt="拡大画像" class="w-[1200px] h-[800px] object-contain">
                </div>
            </div>
        </div>
        <footer class="text-center py-4 text-white text-lg mt-20">
            © 2025 Tomoto
        </footer>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const openModalButton = document.getElementById('openModal');
            const postModal = document.getElementById('postModal');
            const closeModalButton = document.getElementById('closeModal');

            if (openModalButton) {
                openModalButton.addEventListener('click', function() {
                    postModal.classList.remove('hidden');
                    postModal.classList.add('flex');
                });
            }

            if (closeModalButton) {
                closeModalButton.addEventListener('click', function() {
                    postModal.classList.add('hidden');
                    postModal.classList.remove('flex');
                });
            }
            if (postModal) {
                postModal.addEventListener('click', function(event) {
                    // クリックしたのがモーダルの背景だった場合のみ閉じる
                    if (event.target === postModal) {
                        postModal.classList.add('hidden');
                        postModal.classList.remove('flex');
                    }
                });
            }

            const tagInput = document.getElementById("tagInput");
            const tagSuggestions = document.getElementById("tagSuggestions");
            const selectedTagsContainer = document.getElementById("selectedTagsContainer");
            const form = document.querySelector('#postForm');
            let selectedTagIndex = -1;

            let selectedTags = [];

            //タグ候補の表示
            function showTagSuggestions(input) {
                fetch(`/tags/search?query=${input}`)
                    .then(response => {
                        if (response.status === 419 || response.status === 401) {
                            alert('セッションが切れました。再度ログインしてください。');
                            window.location.href = '/login';
                            return;
                        }
                        if (!response.ok) throw new Error('通信エラー');
                        return response.json();
                    })
                    .then(tags => {
                        tagSuggestions.innerHTML = "";
                        if (tags.length === 0) {
                            tagSuggestions.classList.add("hidden");
                            return;
                        }

                        tags.forEach(tag => {
                            const suggestion = document.createElement("div");
                            suggestion.textContent = tag.name;
                            suggestion.classList.add("px-2", "py-1", "bg-[var(--white)]", "hover:bg-gray-200", "cursor-pointer", "border-b", "border-gray-300");

                            suggestion.addEventListener("click", function() {
                                addTag(tag.name);
                            });

                            tagSuggestions.appendChild(suggestion);
                        });

                        tagSuggestions.classList.remove("hidden");
                    })
                    .catch(error => {
                        console.error("Error fetching tags:", error);
                    });
            }
            //タグ入力時の処理、候補の表示の処理を呼び出し
            tagInput.addEventListener("input", function() {
                const input = tagInput.value.trim();
                if (input.length < 1) {
                    tagSuggestions.classList.add("hidden");
                    return;
                }
                selectedTagIndex = -1;
                showTagSuggestions(input);
            });

            //タグの追加
            function addTag(tag) {
                if (!selectedTags.includes(tag)) {
                    selectedTags.push(tag);
                    updateSelectedTags();
                }
                tagInput.value = "";
                tagSuggestions.classList.add("hidden");
            }
            //選択されたタグのデザイン
            function updateSelectedTags() {
                selectedTagsContainer.innerHTML = "";
                selectedTags.forEach(tag => {
                    const tagItem = document.createElement("div");
                    tagItem.textContent = tag;
                    tagItem.classList.add("bg-[var(--bg-dark)]", "text-[--white]", "px-4", "py-2", "rounded-full", "text-lg", "m-1", "flex", "items-center");
                    // 削除ボタン
                    const removeButton = document.createElement("span");
                    removeButton.textContent = " ×";
                    removeButton.classList.add("ml-4", "cursor-pointer");
                    removeButton.addEventListener("click", function() {
                        selectedTags = selectedTags.filter(t => t !== tag);
                        updateSelectedTags();
                    });

                    tagItem.appendChild(removeButton);
                    selectedTagsContainer.appendChild(tagItem);
                });
            }

            tagInput.addEventListener("keypress", function(event) {
                if (event.key === "Enter") {
                    event.preventDefault();

                    const suggestionItems = tagSuggestions.querySelectorAll("div");
                    //選択リストからの場合
                    if (selectedTagIndex >= 0 && selectedTagIndex < suggestionItems.length) {
                        const selectedTag = suggestionItems[selectedTagIndex].textContent.trim();
                        addTag(selectedTag);
                    //入力欄からの場合
                    } else if (tagInput.value.trim() !== "") {
                        addTag(tagInput.value.trim());
                    }

                    tagInput.value = "";
                    tagSuggestions.classList.add("hidden");
                    selectedTagIndex = -1;
                }
            });
            // 矢印キーで候補リストを選択
            document.addEventListener("keydown", function(event) {
                const suggestionItems = tagSuggestions.querySelectorAll("div"); // 候補リストのアイテムを取得

                if (event.key === "ArrowUp") {
                    if (selectedTagIndex > 0) {
                        selectedTagIndex--;
                        updateTagSelection(suggestionItems);
                    }
                } else if (event.key === "ArrowDown") {
                    if (selectedTagIndex < suggestionItems.length - 1) {
                        selectedTagIndex++;
                        updateTagSelection(suggestionItems);
                    }
                }
            });
            // 選択されている候補デザインを更新
            function updateTagSelection(items) {
                // すべてのアイテムから選択スタイルを削除
                items.forEach((item) => {
                    item.classList.remove("bg-gray-200");
                });

                // 選択されたアイテムにスタイルを追加
                if (selectedTagIndex >= 0 && selectedTagIndex < items.length) {
                    items[selectedTagIndex].classList.add("bg-gray-200");
                }
            }

            //タグ候補を非表示にする
            document.addEventListener("click", function(event) {
                if (!tagInput.contains(event.target) && !tagSuggestions.contains(event.target)) {
                    tagSuggestions.classList.add("hidden");
                    selectedTagIndex = -1;
                }
            });

            // 入力欄をクリックしたときにタグ候補を表示
            tagInput.addEventListener("click", function() {
                const input = tagInput.value.trim();
                if (input.length >= 1) {
                    showTagSuggestions(input);
                }
            });

            // フォーム送信時に選択したタグを一緒に送信
            form.addEventListener('submit', function() {
                document.querySelector('#tagInput').value = selectedTags.join(',');
            });

            const previewImage = document.getElementById('previewImage');
            const image = document.getElementById('image');
            // 画像プレビュー
            image.addEventListener('change', function(event) {
                const file = event.target.files[0];  // 画像ファイルを取得

                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImage.src = e.target.result;  // プレビュー画像を更新

                        // プレビュー表示エリアを表示
                        const imagePreview = document.getElementById('imagePreview');
                        imagePreview.classList.remove('hidden');  // プレビュー表示エリアを表示
                        imagePreview.classList.add('flex');
                    };
                    reader.readAsDataURL(file);  // ファイルを読み込む
                } else {
                    previewImage.src = '';
                    imagePreview.classList.add('hidden');
                    imagePreview.classList.remove('flex');
                }
            });
            // 画像をクリックするとファイル選択ダイアログを開く
            previewImage.addEventListener('click', function() {
                image.click();  // input要素をクリックしてファイルダイアログを開く
            });

            //いいね機能
            $(document).on('click', '.like-btn', function () {
                const galleryId = $(this).data('gallery-id');
                const button = $(this);
                const likesCountElement = button.closest('.p-4').find('.likes-count'); // .p-4 を使って親要素を取得
                fetch('/like', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        car_gallery_id: galleryId
                    }),
                })
                .then(response => {
                    if (response.status === 419 || response.status === 401) {
                        alert('セッションが切れました。再度ログインしてください。');
                        window.location.href = '/login';
                        return;
                    }
                    if (!response.ok) throw new Error('通信エラー');
                    return response.json();
                })
                .then(data => {
                    if (!data) return;
                    button.text(data.liked ? '❤️' : '🤍');
                    likesCountElement.text(data.likes_count);
                })
                .catch(error => {
                    console.error(error);
                    alert('エラーが発生しました');
                });
            });

            //投稿の詳細の表示と非表示を切り替える
            const postList = document.getElementById('postList');
            postList.addEventListener('click', (event) => {
                if (event.target.closest('.dots-btn')) {
                    event.stopPropagation();
                    const button = event.target.closest('.dots-btn');
                    const popover = button.nextElementSibling;

                    popover.classList.toggle('hidden');

                    //他に詳細を閉じる
                    document.querySelectorAll('.popover').forEach(otherPopover => {
                        if (otherPopover !== popover) {
                            otherPopover.classList.add('hidden');
                        }
                    });
                }
            });
            //ページをクリックしたら詳細を閉じる
            document.addEventListener('click', () => {
                document.querySelectorAll('.popover').forEach(popover => {
                    popover.classList.add('hidden');
                });
            });

            //無限スクロール機能
            let loading = false;
            let hasMorePost = true;
            const likedGalleries = @json($likedGalleries);//いいね済み投稿のIDを取得
            const displayedIds = new Set();//Set は重複しない値だけを保存する特別なオブジェクト
            document.querySelectorAll('[data-gallery-id]').forEach(element => {
                displayedIds.add(parseInt(element.dataset.galleryId, 10));//取得したidを整数に変換し追加する
            });

            //スクロールイベント
            window.addEventListener('scroll', () => {
                if (!hasMorePost) return;

                if (window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 100) {
                    if (!loading) {
                        loading = true;

                        //URLのクエリパラメータとして page と displayed_idsを付け送信データとなる
                        const query = document.querySelector('input[name="query"]')?.value || '';
                        const priceTagId = document.querySelector('select[name="price_tag_id"]')?.value || '';

                        const url = `/top?displayed_ids=${Array.from(displayedIds).join(',')}&query=${encodeURIComponent(query)}&price_tag_id=${priceTagId}`;//encodeURIComponentはURLで安全にデータを送るために必須
                        //データ送信getデータなのでurlに
                        fetch(url, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'// 返ってくるデータはJSONの指定
                            }
                        })
                        .then(response => {
                            if (response.status === 419 || response.status === 401) {
                                alert('セッションが切れました。再度ログインしてください。');
                                window.location.href = '/login';
                                return;
                            }
                            if (!response.ok) throw new Error('通信エラー');
                            return response.json();
                        })
                        .then(posts => {
                            if (posts.length > 0) {
                                const postList = document.getElementById('postList');
                                posts.forEach(post => {
                                    displayedIds.add(post.id);

                                    const postElement = document.createElement('div');
                                    postElement.classList.add('break-inside-avoid', 'rounded-lg', 'overflow-hidden', 'shadow-lg', 'post', 'relative', 'group');
                                    postElement.dataset.galleryId = post.id;

                                    const isLoggedIn = {{ auth()->check() ? 'true' : 'false' }};
                                    const heart = likedGalleries.includes(post.id) ? '❤️' : '🤍';

                                    let likeButtonHtml = isLoggedIn
                                        ? `<button class="like-btn absolute bottom-2 right-2 text-xl opacity-0 group-hover:opacity-100 transition-opacity duration-300" data-gallery-id="${post.id}">${heart}</button>`
                                        : `<p class="text-sm text-white"><a href="{{ route('login') }}" class="underline">ログインでいいね</a></p>`;

                                    const tagsHtml = Array.isArray(post.tags) && post.tags.length > 0
                                        ? post.tags.map(tag => `<span class="bg-gray-200 text-gray-800 px-2 py-1 rounded-lg text-sm">${tag.name}</span>`).join('')
                                        : '';

                                    const detailsHtml = `
                                        <button class="dots-btn absolute top-2 right-2 text-white bg-black bg-opacity-50 rounded-full p-1 focus:outline-none opacity-0 group-hover:opacity-100">
                                            <img src="/images/more_horiz_24dp_E8EAED_FILL0_wght400_GRAD0_opsz24.svg" class="w-5 h-5">
                                        </button>

                                        <!-- 吹き出し詳細情報 -->
                                        <div class="popover absolute top-10 right-2 bg-white text-sm shadow-lg rounded-lg p-4 w-48 hidden z-10">
                                            <p class="font-semibold text-gray-600 text-sm mb-2">${post.title || 'タイトルなし'}</p>
                                            <p class="text-gray-600 text-sm mb-2">${post.price_tag.name || '値段未設定'}</p>
                                            <p class="text-gray-600 text-sm mb-2">${post.likes_count || 0} いいね</p>
                                            <div class="flex flex-wrap gap-1">
                                                ${tagsHtml}
                                            </div>
                                        </div>
                                    `;

                                    // 画像の部分にカーソルを合わせたときに暗くなる効果を追加
                                    postElement.innerHTML = `
                                        <img src="/storage/${post.image_path}" alt="画像" class="w-full h-auto rounded-t-lg group-hover:brightness-50 transition-all duration-300">
                                        ${likeButtonHtml}
                                        ${detailsHtml}
                                    `;

                                    postList.appendChild(postElement);
                                });
                            } else {
                                hasMorePost = false;
                            }
                            loading = false;
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            loading = false;
                        });
                    }
                }
            });
            //検索候補の処理
            const queryInput = document.getElementById('query');
            const suggestions = document.getElementById('suggestions');
            let selectedIndex = -1;

            function displaySuggestions(query) {
                fetch(`/suggestions?q=${query}`)
                    .then(response => {
                        if (response.status === 419 || response.status === 401) {
                            alert('セッションが切れました。再度ログインしてください。');
                            window.location.href = '/login';
                            return;
                        }
                        if (!response.ok) throw new Error('通信エラー');
                        return response.json();
                    })
                    .then(data => {
                        suggestions.innerHTML = ""; // 一度リセット
                        const suggestionsArray = Array.isArray(data) ? data : Object.values(data);

                        if (suggestionsArray.length === 0) {
                            suggestions.classList.add("hidden");
                            return;
                        }

                        suggestionsArray.forEach(item => {
                            const div = document.createElement("div");
                            div.classList.add("suggestion-item", "p-2", "text-lg", "cursor-pointer", "border-b", "border-gray-300", "hover:bg-gray-200");
                            div.innerText = item.name;
                            div.addEventListener("click", function () {
                                queryInput.value = item.name;
                                suggestions.classList.add("hidden");
                                queryInput.form.submit();
                            });
                            suggestions.appendChild(div);
                        });

                        // 候補リストが表示される
                        suggestions.classList.remove("hidden");
                    })
                    .catch(error => {
                        console.error("Error fetching suggestions:", error);
                    });
            }

            if (queryInput) {
                queryInput.addEventListener('input', function () {
                    const query = this.value;

                    if (query.length > 0) {
                        displaySuggestions(query);
                    } else {
                        suggestions.classList.add('hidden');
                    }
                });

                // 入力欄をクリックしたときにタグ候補を表示
                queryInput.addEventListener('click', function (event) {
                    event.stopPropagation();

                    const query = queryInput.value;
                    if (query.length > 0) {
                        displaySuggestions(query);
                    }
                });
            }

            document.addEventListener('keydown', function (event) {
                const suggestionItems = suggestions.querySelectorAll('.suggestion-item');

                if (event.key === 'ArrowUp') {
                    if (selectedIndex > 0) {
                        selectedIndex--;
                        updateSelection(suggestionItems);
                    }
                }

                else if (event.key === 'ArrowDown') {
                    if (selectedIndex < suggestionItems.length - 1) {
                        selectedIndex++;
                        updateSelection(suggestionItems);
                    }
                }

                else if (event.key === 'Enter' && selectedIndex >= 0) {
                    queryInput.value = suggestionItems[selectedIndex].innerText;
                    suggestions.classList.add('hidden');
                }
            });

            function updateSelection(items) {
                items.forEach((item, index) => {
                    if (index === selectedIndex) {
                        item.classList.add('bg-gray-200');
                    } else {
                        item.classList.remove('bg-gray-200');
                    }
                });
            }
            //ページをクリックすると検索候補を非表示する
            document.addEventListener('click', function () {
                suggestions.classList.add('hidden');
                selectedIndex = -1;
            });

            //画像拡大モーダル
            $(document).ready(function() {
                $('.image-thumbnail').on('click', function() {
                    var fullImageUrl = $(this).data('full');
                    $('#modal-image').attr('src', fullImageUrl);
                    $('#modal').removeClass('hidden');
                });

                $('#modal').on('click', function() {
                    $(this).addClass('hidden');
                });
            });

            //バツボタンのクリックイベント
            document.getElementById('closeButton').addEventListener('click', function() {
                document.getElementById('query').value = '';
                document.getElementById('price_tag_id').selectedIndex = 0;
                document.getElementById('searchForm').submit();
            });
        });
    </script>
</body>
</html>

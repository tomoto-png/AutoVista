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
        }
        h1 {
            text-underline-offset: 10px;
        }
    </style>
</head>
<body class="bg-[var(--bg-light-gray)]">
    <div class="max-w-5xl mx-auto">
        <div class="flex items-center justify-center">
            <h1 class="text-xl font-bold p-4 underline">トップ</h1>
            <a href="{{ route('mypage.index') }}" class="text-xl font-bold p-4 opacity-50">マイページ</a>
        </div>
        <div class="container">
            <h2 class="text-lg font-bold mb-4">投稿一覧</h2>
            <button id="openModal" class="">投稿</button>
            <form action="{{ route('top.index') }}" method="GET" class="mb-4 relative  w-64">
                <input type="text" id="query" name="query" placeholder="キーワード検索"
                    class="border rounded px-2 py-1 w-full text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    value="{{ request('query') }}">

                <div id="suggestions" class="hidden absolute bg-white border w-full rounded mt-1 shadow-lg z-10 max-h-36 overflow-y-auto text-sm z-1000"></div>

                <button type="submit" class="bg-blue-500 text-white px-2 py-1 text-sm rounded hover:bg-blue-600">検索</button>
                <a href="{{ route('top.index') }}" class="ml-2 text-gray-500 text-sm hover:text-gray-700">クリア</a>
            </form>
            <div id="postModal" class="hidden fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center  z-50">
                <div class="bg-white p-6 rounded-lg w-96">
                    <h3 class="text-lg font-bold mb-3">投稿</h3>
                    <form id="postForm" enctype="multipart/form-data" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="block text-sm font-medium">タイトル</label>
                            <input type="text" name="title" id="title" class="w-full border-gray-300 rounded">
                        </div>
                        <div class="mb-3">
                            <label class="block text-sm font-medium">画像</label>
                            <input type="file" name="image" id="image" accept="image/*" class="w-full">
                            <div id="imagePreview" class="mt-2"></div>
                        </div>
                        <div class="mb-3">
                            <label class="block text-sm font-medium">値段</label>
                            <select name="price_tag_id" id="price_tag_id" class="w-full border-gray-300 rounded">
                                <option value="">選択してください</option>
                                @foreach($priceTags as $priceTag)
                                    <option value="{{ $priceTag->id }}" {{ old('price_tag_id') == $priceTag->id ? 'selected' : '' }}>
                                        {{ $priceTag->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3 relative">
                            <label class="block text-sm font-medium">タグ</label>
                            <input type="text" id="tagInput" name="tags" class="w-full border-gray-300 rounded" placeholder="タグを入力" autocomplete="off">
                            <div id="tagSuggestions" class="absolute w-full bg-white border border-gray-300 rounded hidden"></div>
                        </div>
                        <div id="selectedTagsContainer" class="mt-2 flex flex-wrap"></div>
                        <div class="flex justify-end">
                            <button type="button" id="closeModal" class="mr-3 text-gray-500">キャンセル</button>
                            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">投稿</button>
                        </div>
                    </form>
                </div>
            </div>

            <div id="postList" class="mt-6 columns-1 sm:columns-2 md:columns-3 gap-4 space-y-4">
                @if(isset($searchResults))
                    <h2 class="text-xl font-bold mb-4">検索結果</h2>
                    @foreach($searchResults as $post)
                        <div class="break-inside-avoid rounded-lg overflow-hidden bg-white shadow-lg post relative group" data-gallery-id="{{ $post->id }}">
                            <img src="{{ asset('storage/' . $post->image_path) }}" alt="画像" class="w-full h-auto rounded-t-lg">

                            <button class="dots-btn absolute top-2 right-2 text-white bg-black bg-opacity-50 rounded-full p-1 focus:outline-none opacity-0 group-hover:opacity-100">
                                <img src="{{ asset('images/more_horiz_24dp_E8EAED_FILL0_wght400_GRAD0_opsz24.svg') }}" class="w-5 h-5">
                            </button>

                            <div class="popover absolute top-10 right-2 bg-white text-sm shadow-lg rounded-lg p-4 w-48 hidden z-10">
                                <p class="font-semibold mb-2">{{ $post->title }}</p>
                                <p class="text-gray-600 mb-2">{{ $post->likes_count }} いいね</p>
                                <div class="flex flex-wrap gap-1">
                                    @foreach($post->tags as $tag)
                                        <span class="bg-gray-200 text-gray-700 px-2 py-1 rounded-lg text-xs">{{ $tag->name }}</span>
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
                                <p class="text-xs text-gray-500 absolute bottom-2 right-2 bg-white bg-opacity-70 rounded px-2">
                                    <a href="{{ route('login') }}" class="underline">ログインするといいねできます</a>
                                </p>
                            @endauth
                        </div>
                    @endforeach
                @else
                    @foreach($recommendedPosts as $recommendedPost)
                        <div class="break-inside-avoid rounded-lg overflow-hidden bg-white shadow-lg post relative group" data-gallery-id="{{ $recommendedPost->id }}">
                            <img src="{{ asset('storage/' . $recommendedPost->image_path) }}" alt="画像" class="w-full h-auto rounded-t-lg">
                            <button class="dots-btn absolute top-2 right-2 text-white bg-black bg-opacity-50 rounded-full p-1 focus:outline-none opacity-0 group-hover:opacity-100 ">
                                <img src="{{ asset('images/more_horiz_24dp_E8EAED_FILL0_wght400_GRAD0_opsz24.svg') }}" class="w-5 h-5">
                            </button>

                            <div class="popover absolute top-10 right-2 bg-white text-sm shadow-lg rounded-lg p-4 w-48 hidden z-10">
                                <p class="font-semibold mb-2">{{ $recommendedPost->title }}</p>
                                <p class="text-gray-600 mb-2">{{ $recommendedPost->likes_count }} いいね</p>
                                <div class="flex flex-wrap gap-1">
                                    @foreach($recommendedPost->tags as $tag)
                                        <span class="bg-gray-200 text-gray-700 px-2 py-1 rounded-lg text-xs">{{ $tag->name }}</span>
                                    @endforeach
                                </div>
                            </div>
                            @auth
                                <button class="like-btn absolute bottom-2 right-2 text-xl opacity-0 group-hover:opacity-100 transition-opacity duration-300" data-gallery-id="{{ $recommendedPost->id }}">
                                    @if(in_array($recommendedPost->id, $likedGalleries))
                                        ❤️
                                    @else
                                        🤍
                                    @endif
                                </button>
                            @else
                                <p class="text-xs text-gray-500">
                                    <a href="{{ route('login') }}" class="underline">ログインするといいねできます</a>
                                </p>
                            @endauth
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
    <script>
        const openModalButton = document.getElementById('openModal');
        const postModal = document.getElementById('postModal');
        const closeModalButton = document.getElementById('closeModal');

        openModalButton.addEventListener('click', function() {
            postModal.classList.remove('hidden');
        });

        closeModalButton.addEventListener('click', function() {
            postModal.classList.add('hidden');
        });
        document.addEventListener("DOMContentLoaded", function() {
            const tagInput = document.getElementById("tagInput");
            const tagSuggestions = document.getElementById("tagSuggestions");
            const selectedTagsContainer = document.getElementById("selectedTagsContainer");
            const form = document.querySelector('#postForm');

            let selectedTags = [];

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            function showTagSuggestions(input) {
                fetch(`/tags/search?query=${input}`)
                    .then(response => response.json())
                    .then(tags => {
                        tagSuggestions.innerHTML = "";
                        if (tags.length === 0) {
                            tagSuggestions.classList.add("hidden");
                            return;
                        }

                        tags.forEach(tag => {
                            const suggestion = document.createElement("div");
                            suggestion.textContent = tag.name;
                            suggestion.classList.add("px-2", "py-1", "hover:bg-gray-200", "cursor-pointer");
                            suggestion.addEventListener("click", function() {
                                addTag(tag.name);
                            });
                            tagSuggestions.appendChild(suggestion);
                        });

                        tagSuggestions.classList.remove("hidden");
                    });
            }

            tagInput.addEventListener("input", function() {
                const input = tagInput.value.trim();
                if (input.length < 1) {
                    tagSuggestions.classList.add("hidden");
                    return;
                }
                showTagSuggestions(input);
            });

            function addTag(tag) {
                if (!selectedTags.includes(tag)) {
                    selectedTags.push(tag);
                    updateSelectedTags();
                }
                tagInput.value = "";
                tagSuggestions.classList.add("hidden");
            }

            document.getElementById('image').addEventListener('change', function(event) {
                const file = event.target.files[0];

                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.alt = '新しい画像プレビュー';
                        img.classList.add('w-32', 'h-32', 'object-cover', 'rounded-lg');
                        const imagePreview = document.getElementById('imagePreview');
                        imagePreview.innerHTML = '';
                        imagePreview.appendChild(img);
                    };
                    reader.readAsDataURL(file);
                }
            });
            function updateSelectedTags() {
                selectedTagsContainer.innerHTML = "";
                selectedTags.forEach(tag => {
                    const tagItem = document.createElement("div");
                    tagItem.textContent = tag;
                    tagItem.classList.add("bg-blue-500", "text-white", "p-2", "rounded", "m-1", "flex", "items-center");
                    // 削除ボタン
                    const removeButton = document.createElement("span");
                    removeButton.textContent = " ×";
                    removeButton.classList.add("ml-2", "cursor-pointer");
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
                    if (tagInput.value.trim() !== "") {
                        addTag(tagInput.value.trim());
                    }
                }
            });

            document.addEventListener("click", function(event) {
                if (!tagInput.contains(event.target) && !tagSuggestions.contains(event.target)) {
                    tagSuggestions.classList.add("hidden");
                }
            });
            // タグ入力欄をクリックしたときにタグ候補を表示
            tagInput.addEventListener("click", function() {
                const input = tagInput.value.trim();
                if (input.length >= 1) {
                    showTagSuggestions(input);
                }
            });
            // フォーム送信時にタグを更新
            form.addEventListener('submit', function(event) {
                document.querySelector('#tagInput').value = selectedTags.join(',');
            });
            //いいね機能
            $(document).on('click', '.like-btn', function () {
                const galleryId = $(this).data('gallery-id');
                const button = $(this);
                const likesCountElement = button.closest('.p-4').find('.likes-count'); // .p-4 を使って親要素を取得

                $.ajax({
                    url: '/top/like',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        car_gallery_id: galleryId,
                    },
                    success: function (response) {
                        if (response.liked) {
                            button.text('❤️');
                        } else {
                            button.text('🤍');
                        }
                        likesCountElement.text(response.likes_count);
                    },
                    error: function () {
                        alert('エラーが発生しました');
                    }
                });
            });
            //詳細吹き出し
            const postList = document.getElementById('postList');

            postList.addEventListener('click', (event) => {
                if (event.target.closest('.dots-btn')) {
                    event.stopPropagation();
                    const button = event.target.closest('.dots-btn');
                    const popover = button.nextElementSibling;

                    popover.classList.toggle('hidden');

                    document.querySelectorAll('.popover').forEach(otherPopover => {
                        if (otherPopover !== popover) {
                            otherPopover.classList.add('hidden');
                        }
                    });
                }
            });
            document.addEventListener('click', () => {
                document.querySelectorAll('.popover').forEach(popover => {
                    popover.classList.add('hidden');
                });
            });

            let page = 1;
            let loading = false;
            let hasMorePost = true;
            const likedGalleries = @json($likedGalleries);
            const displayedIds = new Set();
            document.querySelectorAll('[data-gallery-id]').forEach(element => {
                displayedIds.add(parseInt(element.dataset.galleryId, 10));
            });

            window.addEventListener('scroll', () => {
                if (!hasMorePost) return;

                if (window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 50) {
                    if (!loading) {
                        loading = true;
                        page++;
                        const url = `/top?page=${page}&displayed_ids=${Array.from(displayedIds).join(',')}`;

                        fetch(url, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(posts => {
                            if (posts.length > 0) {
                                const postList = document.getElementById('postList');
                                posts.forEach(post => {
                                    displayedIds.add(post.id);

                                    const postElement = document.createElement('div');
                                    postElement.classList.add('break-inside-avoid', 'rounded-lg', 'overflow-hidden', 'bg-white', 'shadow-lg', 'post', 'relative', 'group');
                                    postElement.dataset.galleryId = post.id;

                                    const isLoggedIn = {{ auth()->check() ? 'true' : 'false' }};
                                    const heart = likedGalleries.includes(post.id) ? '❤️' : '🤍';

                                    let likeButtonHtml = isLoggedIn
                                        ? `<button class="like-btn absolute bottom-2 right-2 text-xl opacity-0 group-hover:opacity-100 transition-opacity duration-300" data-gallery-id="${post.id}">${heart}</button>`
                                        : `<p class="text-xs text-gray-500"><a href="{{ route('login') }}" class="underline">ログインするといいねできます</a></p>`;

                                    const tagsHtml = Array.isArray(post.tags) && post.tags.length > 0
                                        ? post.tags.map(tag => `<span class="bg-gray-200 text-gray-800 px-2 py-1 rounded-lg text-xs">${tag.name}</span>`).join('')
                                        : '';

                                    const detailsHtml = `
                                        <button class="dots-btn absolute top-2 right-2 text-white bg-black bg-opacity-50 rounded-full p-1 focus:outline-none opacity-0 group-hover:opacity-100">
                                            <img src="/images/more_horiz_24dp_E8EAED_FILL0_wght400_GRAD0_opsz24.svg" class="w-5 h-5">
                                        </button>

                                        <!-- 吹き出し詳細情報 -->
                                        <div class="popover absolute top-10 right-2 bg-white text-sm shadow-lg rounded-lg p-4 w-48 hidden z-10">
                                            <p class="font-semibold mb-2">${post.title || 'タイトルなし'}</p>
                                            <p class="text-gray-600 mb-2">${post.likes_count || 0} いいね</p>
                                            <div class="flex flex-wrap gap-1">
                                                ${tagsHtml}
                                            </div>
                                        </div>
                                    `;

                                    postElement.innerHTML = `
                                        <img src="/storage/${post.image_path}" alt="画像" class="w-full h-auto rounded-t-lg">
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
            const queryInput = document.getElementById('query');
            const suggestions = document.getElementById('suggestions');
            let selectedIndex = -1;
            function displaySuggestions(query) {
                fetch(`/suggestions?q=${query}`)
                    .then(response => response.json())
                    .then(data => {
                    suggestions.innerHTML = "";
                    console.log("サジェストデータ:", data);

                    if (data.length > 0) {
                        suggestions.classList.remove("hidden");

                        data.forEach(item => {
                            const div = document.createElement("div");
                            div.classList.add("suggestion-item", "p-2", "cursor-pointer");
                            div.innerText = item.name;

                            div.addEventListener("click", function () {
                                queryInput.value = item.name;
                                suggestions.classList.add("hidden");
                            });

                            suggestions.appendChild(div);
                        });
                    } else {
                        suggestions.classList.add("hidden");
                    }
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
                        item.classList.add('bg-blue-300');
                    } else {
                        item.classList.remove('bg-blue-300');
                    }
                });
            }
            document.addEventListener('click', function () {
                suggestions.classList.add('hidden');
            });
        });
    </script>
</body>
</html>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>マイページ</title>
    <style>
        :root {
            --bg-dark: #27262a;
            --bg-light-gray: #b8bcc3;
            --text-main: #0f131b;
            --accent-color: #d1d1d5;
            --button-hover: #c1c1c4;
        }
        button {
            text-underline-offset: 5px;
        }
    </style>
</head>
<body class="bg-[var(--bg-light-gray)] text-[var(--text-main)]">
    <div class="flex items-center justify-center">
        <a href="{{ route('top.index') }}" class="text-xl font-bold p-4">トップ</a>
        <h1 class="text-xl font-bold p-4">マイページ</h1>
    </div>
    <div class="max-w-5xl mx-auto p-6 bg-white rounded-xl shadow-lg mt-10">
        <section class="text-center">
            @if ($user->avatar)
                <div class="w-24 h-24 sm:w-32 sm:h-32 mx-auto rounded-full border-4 border-[var(--accent-color)] shadow-lg overflow-hidden">
                    <img class="w-full h-full object-cover"
                        src="{{ asset('storage/' . $user->avatar) }}"
                        alt="{{ $user->name }}のアバター">
                </div>
            @else
                <div class="w-24 h-24 sm:w-32 sm:h-32 mx-auto flex items-center justify-center rounded-full bg-gray-300 text-2xl font-bold text-gray-700 shadow-lg">
                    {{ $user->name ?? (string) $user->id }}
                </div>
            @endif

            <h2 class="mt-4 text-lg font-semibold">{{ $user->name ?? (string) $user->id }}</h2>
        </section>

        <div class="flex mt-6">
            <h3 class="text-lg font-semibold mb-2">自己紹介</h3>
            <p class="text-gray-700 bg-gray-100 p-4 rounded-lg">
                {{ $user->text ?? '自己紹介はまだ設定されていません。' }}
            </p>
            <a href="{{ route('mypage.edit') }}" class="inline-block bg-[var(--accent-color)] text-white px-6 py-2 rounded-full shadow hover:bg-[var(--button-hover)]">
                プロフィール編集
            </a>
        </div>
        <h2>
            <button id="togglePosts" class="underline">投稿一覧</button>
            <button id="toggleLikes" class="opacity-70">いいね一覧</button>
        </h2>

        <div id="posts" class="mt-6 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
            @foreach($userPosts as $post)
                <div class="p-2 border rounded-lg">
                    <img src="{{ asset('storage/' . $post->image_path) }}" alt="画像" class="w-full h-64 object-cover">
                    <form action="{{ route('mypage.destroy', $post->id) }}" method="POST" onsubmit="return confirm('本当に削除しますか？');" class="w-auto">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="">
                            <img src="{{ asset('images/delete_24dp_E8EAED_FILL0_wght400_GRAD0_opsz24.svg') }}" class="w-7 h-7">
                        </button>
                    </form>
                    <button class="openEdit" data-id="{{ $post->id }}">編集</button>
                </div>
            @endforeach
        </div>

        <div id="likes" class="mt-6 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4" style="display: none;">
            @foreach($likedPosts as $like)
                <div class="p-4 border rounded-lg mb-4">
                    <img src="{{ asset('storage/' . $like->image_path) }}" alt="画像" class="w-full h-64 object-cover">
                    <button class="like-btn" data-gallery-id="{{ $like->id }}">
                        @if(in_array($like->id, $likedGalleries))
                            ❤️
                        @else
                            🤍
                        @endif
                    </button>
                </div>
            @endforeach
        </div>
        <div id="editModal" class="hidden fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center">
            <div class="modal-content bg-white rounded-lg p-6 w-full max-w-md">
                <h3 class="text-lg font-bold mb-4">投稿編集</h3>
                <form id="editForm" action="" method="POST"  enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">タイトル</label>
                        <input type="text" name="title" id="title" class="w-full border-gray-300 rounded p-2" placeholder="タイトルを入力">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">画像</label>
                        <input type="file" name="image" id="image" accept="image/*" class="w-full border-gray-300 rounded p-2">
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
                    <div class="mb-4 relative">
                        <label class="block text-sm font-medium mb-1">タグ</label>
                        <input type="text" id="tagInput" name="tags" class="w-full border-gray-300 rounded p-2" placeholder="タグを入力" autocomplete="off">
                        <div id="tagSuggestions" class="absolute w-full bg-white border border-gray-300 rounded hidden mt-1"></div>
                    </div>
                    <div id="selectedTagsContainer" class="mt-2 flex flex-wrap gap-2"></div>
                    <div class="flex justify-end mt-4">
                        <button type="button" id="closeEdit" class="mr-3 text-gray-500 hover:text-gray-700">キャンセル</button>
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">投稿</button>
                    </div>
                </form>
            </div>
        </div>
        <div id="posts-pagination" data-next-url="{{ $userPosts->nextPageUrl() }}"></div>
        <div id="likes-pagination" data-next-url="{{ $likedPosts->nextPageUrl() }}"></div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const editModal = document.getElementById('editModal');
            const closeEditlButton = document.getElementById('closeEdit');

            const tagInput = document.getElementById("tagInput");
            const tagSuggestions = document.getElementById("tagSuggestions");
            const selectedTagsContainer = document.getElementById("selectedTagsContainer");
            const editForm = document.querySelector('#editForm');

            let selectedTags = [];
            let currentGalleryId;

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $(document).on('click', '.openEdit', function() {
                const currentGalleryId = $(this).data('id');
                openEditModal(currentGalleryId);
            });

            closeEditlButton.addEventListener('click', function() {
                editModal.classList.add('hidden');
            });

            function openEditModal(galleryId) {
                fetch(`mypage/gallery/${galleryId}/edit`)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('editForm').action = `/mypage/gallery/${data.id}`;
                        document.getElementById('title').value = data.title;

                        const imagePreview = document.getElementById('imagePreview');
                        if (data.image_path) {
                            const img = document.createElement('img');
                            img.src = `/storage/${data.image_path}`;
                            img.alt = '画像プレビュー';
                            img.classList.add('w-32', 'h-32', 'object-cover', 'rounded-lg');

                            imagePreview.innerHTML = '';
                            imagePreview.appendChild(img);
                        } else {
                            imagePreview.innerHTML = '画像がありません';
                        }

                        const priceTagSelect = document.getElementById('price_tag_id');
                        if (data.price_tag_id) {
                            priceTagSelect.value = data.price_tag_id;
                        } else {
                            priceTagSelect.value = '';
                        }

                        if (Array.isArray(data.tags)) {
                            selectedTags = data.tags.map(tag => tag.name);
                            updateSelectedTags();
                        } else {
                            console.error("tags is not an array:", data.tags);
                        }

                        editModal.classList.remove('hidden');
                    })
                    .catch(error => {
                        console.error("Error fetching gallery data:", error);
                    });
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

            // タグ候補を表示する
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

            // 選択されたタグの表示を更新
            function updateSelectedTags() {
                selectedTagsContainer.innerHTML = "";
                selectedTags.forEach(tag => {
                    const tagItem = document.createElement("div");
                    tagItem.textContent = tag;
                    tagItem.classList.add("bg-blue-500", "text-white", "p-2", "rounded", "m-1", "flex", "items-center");

                    // タグ削除ボタン
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

            // Enterキーでタグ追加またフォームの送信制約
            tagInput.addEventListener("keypress", function(event) {
                if (event.key === "Enter") {
                    event.preventDefault();
                    if (tagInput.value.trim() !== "") {
                        addTag(tagInput.value.trim());
                    }
                }
            });

            // クリック以外の場所を押したときに候補を非表示
            document.addEventListener("click", function(event) {
                if (!tagInput.contains(event.target) && !tagSuggestions.contains(event.target)) {
                    tagSuggestions.classList.add("hidden");
                }
            });

            tagInput.addEventListener("click", function() {
                const input = tagInput.value.trim();
                if (input.length >= 1) {
                    showTagSuggestions(input);
                }
            });

            // 編集フォーム送信時にタグ情報を一緒に送信
            editForm.addEventListener('submit', function(event) {
                console.log(selectedTags);
                document.querySelector('#tagInput').value = selectedTags.join(',');
            });

            // いいね一覧と投稿一覧切り替え
            $(document).ready(function() {
                let currentTab = 'posts';
                let loading = false;
                $("#togglePosts").addClass("underline");
                $("#toggleLikes").removeClass("underline").addClass("opacity-50");

                $("#togglePosts").click(function() {
                    $("#posts").show();
                    $("#likes").hide();
                    currentTab = 'posts';

                    $(this).removeClass("opacity-50").addClass("underline");
                    $("#toggleLikes").removeClass("underline").addClass("opacity-50");
                });

                $("#toggleLikes").click(function() {
                    $("#likes").show();
                    $("#posts").hide();
                    currentTab = 'likes';

                    $(this).removeClass("opacity-50").addClass("underline");
                    $("#togglePosts").removeClass("underline").addClass("opacity-50");
                });
                $(window).scroll(function() {
                    if (loading) return;
                    if ($(window).scrollTop() + $(window).height() >= $(document).height() - 50) {
                        let nextPageLink = (currentTab === 'posts')
                            ? $('#posts-pagination').data('next-url')
                            : $('#likes-pagination').data('next-url');

                        if (nextPageLink) {
                            loading = true;
                            loadMoreData(nextPageLink);
                        }
                    }
                });

                function loadMoreData(url) {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    $.get(url, function(data) {
                        if (currentTab === 'posts') {
                            data.userPosts.data.forEach(function(post) {
                                const postHTML = `
                                    <div class="p-2 border rounded-lg">
                                        <img src="/storage/${post.image_path}" alt="画像" class="w-full h-64 object-cover">
                                            <form action="/mypage/${post.id}" method="POST" onsubmit="return confirm('本当に削除しますか？');" class="w-auto">
                                                <input type="hidden" name="_method" value="DELETE">
                                                <input type="hidden" name="_token" value="${csrfToken}">
                                                <button type="submit" class="">
                                                    <img src="/images/delete_24dp_E8EAED_FILL0_wght400_GRAD0_opsz24.svg" class="w-7 h-7">
                                                </button>
                                            </form>
                                        <button class="openEdit" data-id="${post.id}">編集</button>
                                    </div>
                                `;
                                $('#posts').append(postHTML);
                            });
                        } else {
                            const likedGalleries = @json($likedGalleries);

                            data.likedPosts.data.forEach(function(like) {
                                const isLiked = likedGalleries.includes(like.id);

                                const likeHTML = `
                                    <div class="p-4 border rounded-lg mb-4">
                                        <img src="/storage/${like.image_path}" alt="画像" class="w-full h-64 object-cover">

                                        <!-- いいねボタン -->
                                        <button class="like-btn" data-gallery-id="${like.id}">
                                            ${isLiked ? '❤️' : '🤍'}
                                        </button>
                                    </div>
                                `;
                                $('#likes').append(likeHTML);
                            });
                        }

                        if (currentTab === 'posts') {
                            $('#posts-pagination').data('next-url', data.userPosts.next_page_url);
                        } else {
                            $('#likes-pagination').data('next-url', data.likedPosts.next_page_url);
                        }

                        loading = false;
                    }).fail(function() {
                        console.log('データの取得に失敗しました。');
                        loading = false;
                    });
                }

                // いいね機能
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
            });
        });
    </script>
</body>
</html>

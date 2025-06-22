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
            --white: #ffffff;
            --hover: #a1a5ab;
            --bg-hover: #383b42;
        }
        button {
            text-underline-offset: 5px;
        }

        h1 {
            text-underline-offset: 10px;
        }
    </style>
</head>
<body class="bg-[var(--bg-dark)] text-[var(--white)]">
    <div class="max-w-5xl mx-auto relative">
        <div class="flex items-center justify-center fixed top-0 left-0 w-full z-50 mt-5">
            <a href="{{ route('top.index') }}" class="text-2xl font-bold p-4 hover:scale-105 opacity-60">おすすめ</a>
            <h1 class="text-3xl font-bold p-4 hover:scale-105 underline">マイページ</h1>
        </div>
        <div class="p-6 bg-[var(--bg-light-gray)] rounded-xl shadow-lg mt-40">
            <section class="text-center">
                @if ($user->avatar)
                    <div class="w-32 h-32 sm:w-44 sm:h-44 mx-auto rounded-full border-4 border-[var(--accent-color)] shadow-lg overflow-hidden">
                        <img class="w-full h-full object-cover"
                            src="{{ asset('storage/' . $user->avatar) }}"
                            alt="{{ $user->name }}のアバター">
                    </div>
                @else
                    <div class="w-24 h-24 sm:w-32 sm:h-32 mx-auto flex items-center justify-center rounded-full bg-gray-300 text-2xl font-bold text-[var(--text-main)] shadow-lg">
                        {{ $user->name ?? (string) $user->id }}
                    </div>
                @endif

                <h2 class="mt-4 text-2xl font-semibold text-[var(--text-main)]">{{ $user->name ?? (string) $user->id }}</h2>
            </section>

            <div class="mt-6 p-6 bg-[var(--bg-dark)] shadow-md rounded-lg">
                <div class="flex items-center justify-between mb-4 flex-wrap">
                    <h3 class="text-2xl text-[var(--white)] font-semibold w-full sm:w-auto">自己紹介</h3>
                    <div class="flex items-center ml-auto w-full sm:w-auto justify-between sm:justify-start">
                        <a href="{{ route('mypage.edit') }}" class="inline-block text-lg bg-[var(--bg-light-gray)] text-[var(--text-main)] px-4 py-2 rounded-lg shadow-md hover:bg-[var(--hover)] transition-colors duration-300 w-full sm:w-auto mb-2 sm:mb-0 whitespace-nowrap">
                            プロフィール編集
                        </a>
                        <!-- ログアウトボタンを右端に配置 -->
                        <form action="{{ route('logout') }}" method="POST" class="inline ml-3 w-full sm:w-auto">
                            @csrf
                            <button type="submit" class="bg-red-400 text-lg text-white px-4 py-2 rounded-lg hover:bg-red-600 w-full sm:w-auto">
                                ログアウト
                            </button>
                        </form>
                    </div>
                </div>

                <p class="text-[var(--text-main)] bg-[var(--white)] text-lg p-4 rounded-lg">
                    {{ $user->text ?? '自己紹介はまだ設定されていません。' }}
                </p>
            </div>
            <h2 class="flex justify-center space-x-6 items-center text-[var(--text-main)] mt-6">
                <button id="togglePosts" class="text-2xl font-medium focus:outline-none underline transition-all duration-300 hover:scale-105">投稿一覧</button>
                <button id="toggleLikes" class="text-2xl font-medium opacity-70 focus:outline-none transition-all duration-300 hover:scale-105">いいね一覧</button>
            </h2>

            <div id="posts" class="mt-6 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                @foreach($userPosts as $post)
                    <!-- 画像を包むdivにrelativeとgroupクラスを追加 -->
                    <div class="relative group">
                        <img src="{{ asset('storage/' . $post->image_path) }}" alt="画像" class="w-full h-64 rounded-lg object-cover transition-all duration-300 group-hover:filter group-hover:brightness-50">
                        <div class="absolute bottom-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center">
                            <button class="openEdit" data-id="{{ $post->id }}">
                                <img src="{{ asset('images/edit_24dp_E8EAED_FILL0_wght400_GRAD0_opsz24.svg') }}" class="w-6 h-6">
                            </button>
                            <form action="{{ route('mypage.destroy', $post->id) }}" method="POST" onsubmit="return confirm('本当に削除しますか？');" class="w-auto ml-2">
                                @csrf
                                @method('DELETE')
                                <button type="submit">
                                    <img src="{{ asset('images/delete_24dp_E8EAED_FILL0_wght400_GRAD0_opsz24.svg') }}" class="w-7 h-7">
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            <div id="likes" class="mt-6 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4" style="display: none;">
                @foreach($likedPosts as $like)
                    <div class="relative">
                        <img src="{{ asset('storage/' . $like->image_path) }}" alt="画像" class="w-full h-64 rounded-lg image-thumbnail cursor-pointer object-cover" data-full="{{ asset('storage/' . $like->image_path) }}">
                        <button class="dots-btn absolute top-2 right-2 text-white bg-black bg-opacity-50 rounded-full p-1">
                            <img src="{{ asset('images/more_horiz_24dp_E8EAED_FILL0_wght400_GRAD0_opsz24.svg') }}" class="w-5 h-5">
                        </button>

                        <div class="popover absolute top-10 right-2 bg-white text-sm shadow-lg rounded-lg p-4 w-48 hidden z-10">
                            <p class="font-semibold text-sm text-gray-600 mb-2">{{ $like->title }}</p>
                            <p class="text-gray-600 text-sm mb-2">{{ $like->priceTag->name ?? '値段未設定' }}</p>
                            <p class="text-gray-600 text-sm mb-2">{{ $like->likes_count }} いいね</p>
                            <div class="flex flex-wrap gap-1">
                                @foreach($like->tags as $tag)
                                    <span class="bg-gray-200 text-gray-700 px-2 py-1 rounded-lg text-sm">{{ $tag->name }}</span>
                                @endforeach
                            </div>
                        </div>
                        <button class="like-btn absolute bottom-2 right-2" data-gallery-id="{{ $like->id }}">
                            ❤️
                        </button>
                    </div>
                @endforeach

                <div id="modal" class="fixed inset-0 bg-black bg-opacity-50 hidden justify-center items-center z-50">
                    <div class="modal-content flex justify-center items-center w-full h-full">
                        <img id="modal-image" src="" alt="拡大画像" class="w-[1200px] h-[800px] object-contain">
                    </div>
                </div>
            </div>
            <div id="editModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="modal-content bg-[var(--bg-light-gray)] p-8 rounded-2xl shadow-2xl w-full max-w-3xl text-[var(--text-main)] relative">
                    <h3 class="text-3xl font-bold text-gray-800 mb-6 border-b pb-2">投稿編集</h3>
                    <form id="editForm" action="" method="POST"  enctype="multipart/form-data" class="space-y-5">
                        @csrf
                        @method('PUT')
                        <!-- 2カラムグリッドレイアウト -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            <!-- 左カラム: タイトル & 画像 -->
                            <div class="space-y-4">
                                <!-- タイトル入力 -->
                                <div>
                                    <input type="text" name="title" id="title"
                                        class="w-full text-lg border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-400 focus:outline-none"
                                        placeholder="タイトルを入力">
                                </div>
                                <!-- 値段選択 -->
                                <div>
                                    <select name="price_tag_id" id="price_tag_id"
                                        class="w-full text-lg border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-400 focus:outline-none">
                                        <option value="">値段を選択してください</option>
                                        @foreach($priceTags as $priceTag)
                                            <option value="{{ $priceTag->id }}" {{ old('price_tag_id') == $priceTag->id ? 'selected' : '' }}>
                                                {{ $priceTag->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="flex justify-center">
                                    <div class="bg-[var(--white)] relative w-64 h-64">
                                        <!-- プレビュー表示エリア（アイコンの上に被せる） -->
                                        <div id="imagePreview" class="absolute inset-0 hidden flex items-center justify-center z-10">
                                            <img id="previewImage" src="" alt="プレビュー画像" class="w-full h-full object-cover rounded-lg shadow-md cursor-pointer">
                                        </div>

                                        <!-- アイコンボタン -->
                                        <label for="image" class="cursor-pointer flex flex-col items-center justify-center w-full h-full border-2 border-dashed border-gray-300 rounded-lg hover:bg-gray-100 transition z-20">
                                            <img src="{{ asset('images/imag.svg') }}" alt="画像アップロード" class="w-12 h-12 opacity-70">
                                            <span class="mt-2 text-base text-gray-600">画像を選択</span>
                                            <input type="file" name="image" id="image" accept="image/*" class="hidden">
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- 右カラム: 値段 & タグ -->
                            <div class="space-y-4">
                                <!-- タグ入力 -->
                                <div class="relative">
                                    <input type="text" id="tagInput" name="tags"
                                        class="w-full text-lg border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-400 focus:outline-none"
                                        placeholder="タグを入力(複数可能)" autocomplete="off">
                                    <div id="tagSuggestions"
                                        class="absolute w-full bg-white border border-gray-300 rounded-lg mt-1 hidden z-10 shadow-lg"></div>
                                </div>

                                <!-- 選択されたタグの表示 -->
                                <div id="selectedTagsContainer" class="flex flex-wrap gap-2 mt-2"></div>
                            </div>
                        </div>

                        <!-- ボタン -->
                        <div class="flex justify-end space-x-3 mt-6">
                            <button type="button" id="closeEdit"
                                class="px-4 py-2 text-lg rounded-lg bg-[var(--bg-dark)] text-[var(--white)] hover:bg-[var(--bg-hover)] transition">キャンセル</button>
                            <button type="submit"
                                class="px-6 py-2 text-lg bg-[var(--bg-dark)] text-[var(--white)] rounded-lg hover:bg-[var(--bg-hover)] transition shadow-md">更新</button>
                        </div>
                    </form>
                </div>
            </div>
            <div id="posts-pagination" data-next-url="{{ $userPosts->nextPageUrl() }}"></div>
            <div id="likes-pagination" data-next-url="{{ $likedPosts->nextPageUrl() }}"></div>
        </div>
        <footer class="text-center py-4 text-white text-lg mt-20">
            © 2025 Tomato
        </footer>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const editModal = document.getElementById('editModal');
            const closeEditlButton = document.getElementById('closeEdit');

            const tagInput = document.getElementById("tagInput");
            const tagSuggestions = document.getElementById("tagSuggestions");
            const selectedTagsContainer = document.getElementById("selectedTagsContainer");
            const editForm = document.querySelector('#editForm');

            let selectedTagIndex = -1;
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
                        const previewImage = document.getElementById('previewImage');

                        // 要素が存在するか確認
                        if (imagePreview && previewImage) {
                            // 画像がある場合の処理
                            if (data.image_path) {
                                previewImage.src = `/storage/${data.image_path}`;
                                previewImage.alt = '画像プレビュー';
                                previewImage.classList.add('w-full', 'h-full', 'object-cover', 'rounded-lg');
                                imagePreview.classList.remove('hidden'); // 画像がある場合、プレビューを表示
                            } else {
                                previewImage.src = '';
                                previewImage.alt = '画像がありません';
                                imagePreview.classList.add('hidden'); // 画像がない場合は隠す
                            }
                        } else {
                            console.error('画像プレビューエリアまたは画像要素が見つかりません');
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
            // 画像変更後の処理
            document.getElementById('image').addEventListener('change', function(event) {
                const file = event.target.files[0];  // 画像ファイルを取得

                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const previewImage = document.getElementById('previewImage');
                        const imagePreview = document.getElementById('imagePreview');

                        // 画像プレビュー要素が存在することを確認
                        if (previewImage && imagePreview) {
                            previewImage.src = e.target.result;  // プレビュー画像を更新
                            imagePreview.classList.remove('hidden');  // プレビュー表示エリアを表示
                        } else {
                            console.error('画像プレビュー要素が見つかりません');
                        }
                    };
                    reader.readAsDataURL(file);  // ファイルを読み込む
                }
            });

            // 画像をクリックするとファイル選択ダイアログを開く
            document.getElementById('previewImage').addEventListener('click', function() {
                document.getElementById('image').click();  // input要素をクリックしてファイルダイアログを開く
            });
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
                            suggestion.classList.add("px-2", "py-1", "text-lg", "hover:bg-gray-200", "cursor-pointer");
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
                selectedTagIndex = -1;
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
                    tagItem.classList.add("bg-[var(--bg-dark)]", "text-[--white]", "px-4", "py-2", "rounded-full", "text-lg", "m-1", "flex", "items-center");

                    // タグ削除ボタン
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

            tagInput.addEventListener("keydown", function(event) {
                const suggestionItems = tagSuggestions.children;

                if (event.key === "ArrowDown") {
                    event.preventDefault();
                    if (selectedTagIndex < suggestionItems.length - 1) {
                        selectedTagIndex++;
                        updateTagSelection(suggestionItems);
                    }
                } else if (event.key === "ArrowUp") {
                    event.preventDefault();
                    if (selectedTagIndex > 0) {
                        selectedTagIndex--;
                        updateTagSelection(suggestionItems);
                    }
                } else if (event.key === "Enter") {
                    if (selectedTagIndex >= 0 && selectedTagIndex < suggestionItems.length) {
                        addTag(suggestionItems[selectedTagIndex].textContent);
                    }
                }
            });

            function updateTagSelection(items) {
                Array.from(items).forEach((item, index) => {
                    if (index === selectedTagIndex) {
                        item.classList.add("bg-gray-200");
                    } else {
                        item.classList.remove("bg-gray-200");
                    }
                });
            }

            // Enterキーでタグ追加またフォームの送信制約
            tagInput.addEventListener("keypress", function(event) {
                if (event.key === "Enter") {
                    event.preventDefault();
                    if (selectedTagIndex >= 0) {
                        const tag = tagSuggestions.children[selectedTagIndex];
                        addTag(tag.textContent);
                    } else if (tagInput.value.trim() !== "") {
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
                    fetchPosts('user');
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
                    if ($(window).scrollTop() + $(window).height() >= $(document).height() - 200) {
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
                                    <div class="group relative">
                                        <img src="/storage/${post.image_path}" alt="画像" class="w-full h-64 rounded-lg object-cover transition-all duration-300 group-hover:filter group-hover:brightness-50">
                                        <div class="absolute bottom-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex space-x-2 items-center">
                                            <button class="openEdit" data-id="${post.id}">
                                                <img src="/images/edit_24dp_E8EAED_FILL0_wght400_GRAD0_opsz24.svg" class="w-7 h-7">
                                            </button>
                                            <form action="/mypage/${post.id}" method="POST" onsubmit="return confirm('本当に削除しますか？');" class="w-auto ml-2">
                                                <input type="hidden" name="_method" value="DELETE">
                                                <input type="hidden" name="_token" value="${csrfToken}">
                                                <button type="submit">
                                                    <img src="/images/delete_24dp_E8EAED_FILL0_wght400_GRAD0_opsz24.svg" class="w-6 h-6">
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                `;
                                $('#posts').append(postHTML);
                            });
                        } else {
                            data.likedPosts.data.forEach(function(like) {
                                const likeHTML = `
                                    <div class="relative">
                                        <img src="/storage/${like.image_path}" alt="画像" class="w-full h-64 rounded-lg cursor-pointer object-cover">

                                        <!-- いいねボタン -->
                                        <button class="like-btn absolute bottom-2 right-2" data-gallery-id="${like.id}">
                                            ❤️
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
                        error: function(xhr) {
                            if (xhr.status === 419 || xhr.status === 401) {
                                alert('セッションが切れました。再度ログインしてください。');
                                window.location.href = '/login';
                            } else {
                                alert('エラーが発生しました');
                            }
                        }
                    });
                });
            });

            $(document).ready(function() {
                // サムネイル画像がクリックされた時
                $('.image-thumbnail').on('click', function() {
                    // data-full 属性から拡大画像のURLを取得
                    var fullImageUrl = $(this).data('full');
                    // モーダル内の画像に設定
                    $('#modal-image').attr('src', fullImageUrl);
                    // モーダルを表示
                    $('#modal').removeClass('hidden');
                });

                // モーダル自体がクリックされた時にモーダルを非表示にする
                $('#modal').on('click', function() {
                    $(this).addClass('hidden');
                });
            });


            const likes = document.getElementById('likes');

            likes.addEventListener('click', (event) => {
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
        });
    </script>
</body>
</html>

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CarGallery;
use App\Models\Tag;
use App\Models\Like;
use App\Models\Recommendation;
use App\Models\PriceTag;
use Illuminate\Support\Facades\DB;

class TopController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $likedGalleries = [];
        //表示してるページ数を代入
        $page = $request->get('page', 1);

        $priceTags = PriceTag::all();
        $displayedIds = $request->ajax()//リクエストがAjax通信の判定
            ? array_map('intval', array_filter(explode(',', $request->get('displayed_ids', ''))))//表示したidを取得,array_filter()で空の文字列をを削除, array_map('intval', ...)整数に変換
            : [];
        //ログインしているか
        if ($user) {
            $recommendedPosts = $this->getRecommendeds($user, $page, $displayedIds);
            $likedGalleries = Like::where('user_id', $user->id)
            ->pluck('car_gallery_id')
            ->toArray();
        } else {
            $recommendedPosts = $this->getRandoms($page, $displayedIds);
        }

        if ($request->filled('query') || $request->filled('price_tag_id')){
            $query = $request->input('query');
            $priceTagId = $request->input('price_tag_id');
            $searchResults = CarGallery::when($query, function ($q) use ($query) {
                    $q->where('title', 'LIKE', "%{$query}%")
                    ->orWhereHas('tags', function ($tagQuery) use ($query) {
                        $tagQuery->where('tags.name', 'LIKE', "%{$query}%");
                    });
                })
                ->when($priceTagId, function ($q) use ($priceTagId) {
                    $q->where('price_tag_id', $priceTagId);
                })
                ->skip(($page - 1) * 12)
                ->withCount('likes')
                ->orderByDesc('created_at')
                ->limit(12)
                ->get();

            if ($request->ajax()) {
                return response()->json($searchResults);
            }

            return view('top.index', compact('likedGalleries', 'searchResults', 'priceTags'));
        }

        if ($request->ajax()) {
            return response()->json($recommendedPosts);
        }
        return view('top.index', compact('likedGalleries', 'recommendedPosts', 'priceTags'));
    }

    private function getRecommendeds($user, $page = 1, $displayedIds = [])
    {
        //新しい順の4つのtag_idを取る
        $topTags = Recommendation::where('user_id', $user->id)
            ->where('count', '>', 0)
            ->orderByDesc('count')
            ->limit(4)
            ->pluck('count', 'tag_id')
            ->toArray();

        //いいねデータがない時はgetRandomsを表示
        if (empty($topTags)) {
            return $this->getRandoms($page, $displayedIds);
        }
        //四つのタグのカウント合計
        $totalCount = array_sum($topTags);

        $tagPostCounts = [];
        //投稿数の割合計算何件必要か
        foreach ($topTags as $tagId => $count) {
            $postCountForTag = (int) (($count / $totalCount) * 12);
            $tagPostCounts[$tagId] = max($postCountForTag, 1);
        }

        $recommendedPosts = collect();//Collection オブジェクト を空で初期化

        //計算した投稿数に応じて投稿を取る
        foreach ($tagPostCounts as $tagId => $postCount) {
            $existingIds = $recommendedPosts->pluck('id')->toArray();
            $excludeIds = array_merge($existingIds, $displayedIds);//取得したidと表示したidを一つの配列に結合,重複の表示を防ぐ
            $postsForTag = CarGallery::whereHas('tags', function ($query) use ($tagId) {
                    $query->where('tags.id', $tagId);
                })
                ->whereNotIn('id', $excludeIds)
                ->withCount('likes')
                ->limit($postCount)
                ->get();
            $recommendedPosts = $recommendedPosts->merge($postsForTag);
        }
        $recommendedPosts = $recommendedPosts->unique('id');//同じ投稿が複数含まれている場合、1つだけ残す
        //投稿が12件足りない時の処理
        if ($recommendedPosts->count() < 12) {
            $existingPostIds = $recommendedPosts->pluck('id')->toArray();
            $remainingCount = 12 - $recommendedPosts->count();
            $randomPosts = CarGallery::whereNotIn('id', array_merge($existingPostIds, $displayedIds))
                ->withCount('likes')
                ->orderByDesc('id')
                ->limit($remainingCount)
                ->get();
            $recommendedPosts = $recommendedPosts->merge($randomPosts);
        }
        return $recommendedPosts->shuffle();
    }

    //いいねがデータが足りないときやログインしてない時の表示用
    private function getRandoms($page = 1)
    {
        return CarGallery::withCount('likes')
            ->orderBy('updated_at', 'desc')
            ->skip(($page - 1) * 12)
            ->limit(12)
            ->get();
    }

    public function store(Request $request)
    {
        $userId = Auth::id();
        $request->validate([
            'title' => 'required|max:255',
            'image' => 'nullable|image|max:10240',  // 10MB
            'price_tag_id' => 'required|exists:price_tags,id',
            'tags' => 'nullable|string',
        ], [
            'title.required' => 'タイトルを入力してください。',
            'image.image' => '画像形式でアップロードしてください。',
            'image.max' => '画像は10MB以内でアップロードしてください。',
            'price_tag_id.required' => '値段を選択してください。',
        ]);
        try {
            DB::transaction(function () use ($request, $userId) {
                $imagePath = $request->file('image')->store('images', 'public');

                $gallery = CarGallery::create([
                    'user_id' => $userId,
                    'title' => $request->title,
                    'image_path' => $imagePath,
                    'price_tag_id' => $request->price_tag_id,
                ]);

                if ($request->filled('tags')) {
                    $tagNames = array_filter(array_map(function($tagName) {
                        return trim(mb_convert_kana($tagName, "as"));
                    }, explode(',', $request->tags)));
                    // 既存のタグを一括取得
                    $existingTags = Tag::whereIn('name', $tagNames)->get();
                    $existingNames = $existingTags->pluck('name')->all();
                    $newTagNames = array_diff($tagNames, $existingNames);
                    $newTags = array_map(function($name) {
                        return ['name' => $name, 'created_at' => now(), 'updated_at' => now()];
                    }, $newTagNames);
                    if (!empty($newTags)) {
                        Tag::insert($newTags);
                        $insertedTags = Tag::whereIn('name', $newTagNames)->get();
                        $allTags = $existingTags->concat($insertedTags);
                    } else {
                        $allTags = $existingTags;
                    }
                    $gallery->tags()->attach($allTags->pluck('id')->all());
                }
            });
        } catch (\Exception $e) {
            if (!empty($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
            throw $e;
        }
        return redirect()->route('top.index');
    }
}

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
        $user = $request->user();
        $likedGalleries = [];
        $priceTags = PriceTag::all();
        $displayedIds = $request->ajax()
            ? array_map('intval', array_filter(explode(',', $request->get('displayed_ids')), fn($id) => $id !== ''))//displayed_idのデータをカンマ区切りの文字列値でリストに代入する,また空要素を除外する、最終定期に全ての値を整数に変換
            : [];
        $queryInput = $request->input('query');
        $priceTagId = $request->input('price_tag_id');

        if ($user) {
            $recommendedPosts = $this->getRecommendeds($user, $displayedIds);
            $likedGalleries = Like::where('user_id', $user->id)
                ->pluck('car_gallery_id')
                ->toArray();
        } else {
            $recommendedPosts = $this->getRandoms($displayedIds);
        }

        if ($queryInput || $priceTagId){
            $searchResults = CarGallery::when($queryInput, function ($query) use ($queryInput) {
                    $query->where('title', 'LIKE', "%{$queryInput}%")
                    ->orWhereHas('tags', function ($tagQuery) use ($queryInput) {
                        $tagQuery->where('tags.name', 'LIKE', "%{$queryInput}%");
                    });
                })
                ->when($priceTagId, function ($query) use ($priceTagId) {
                    $query->where('price_tag_id', $priceTagId);
                })
                ->withCount('likes')
                ->latest()
                ->limit(12)
                ->get();
        }

        $posts = $searchResults ?? $recommendedPosts;

        if ($request->ajax()) {
            return response()->json($posts);
        }
        return view('top.index', compact('likedGalleries', 'posts', 'priceTags'));
    }

    private function getRecommendeds($user, $displayedIds = [])
    {
        //新しい順の4つのtag_idを取る
        $topTags = Recommendation::where('user_id', $user->id)
            ->where('count', '>', 0)
            ->orderByDesc('count')
            ->limit(4)
            ->pluck('count', 'tag_id');

        //いいねデータがない時はgetRandomsを表示
        if (empty($topTags)) {
            return $this->getRandoms($displayedIds);
        }
        //四つのタグのカウント合計
        $totalCount = $topTags->sum();
        $tagPostCounts = [];
        $remaining = 12;
        //投稿数の割合計算何件必要か
        foreach ($topTags as $tagId => $count) {
            $ratio = $count / $totalCount;
            $postCount = max(1, (int) round($ratio * 12));
            $tagPostCounts[$tagId] = $postCount;
            $remaining -= $postCount;
        }

        arsort($tagPostCounts);
        while ($remaining < 0) {
            foreach ($tagPostCounts as $tagId => $count) {
                if ($count > 1 && $remaining < 0) {
                    $tagPostCounts[$tagId]--;
                    $remaining++;
                }
            }
        }

        $recommendedPosts = collect();//Collection オブジェクト を空で初期化

        //計算した投稿数に応じて投稿を取る
        foreach ($tagPostCounts as $tagId => $postCount) {
            $postsForTag = CarGallery::whereHas('tags', function ($query) use ($tagId) {
                    $query->where('tags.id', $tagId);
                })
                ->whereNotIn('id', $recommendedPosts->pluck('id')->merge($displayedIds)->all())
                ->withCount('likes')
                ->with(['priceTag', 'tags'])
                ->limit($postCount)
                ->get();

            $recommendedPosts = $recommendedPosts->merge($postsForTag);
        }
        $needed = 12 - $recommendedPosts->count();
        //投稿が12件足りない時の処理
        if ($needed > 0) {
            $randomPosts = CarGallery::whereNotIn('id', $recommendedPosts->pluck('id')->merge($displayedIds)->all())
                ->withCount('likes')
                ->with(['priceTag', 'tags'])
                ->latest()
                ->limit($needed)
                ->get();
            $recommendedPosts = $recommendedPosts->merge($randomPosts);
        }
        return $recommendedPosts;
    }

    //いいねがデータが足りないときやログインしてない時の表示用
    private function getRandoms($displayedIds = [])
    {
        return CarGallery::whereNotIn('id', $displayedIds)
            ->with(['priceTag', 'tags'])
            ->withCount('likes')
            ->latest()
            ->limit(12)
            ->get();
    }

    public function store(Request $request)
    {
        $userId = $request->user()->id;
        $request->validate([
            'title' => 'required|max:255',
            'image' => 'nullable|image|max:2048',
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
                    }
                    $allTags = Tag::whereIn('name', $tagNames)->get();
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

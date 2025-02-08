<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CarGallery;
use App\Models\Tag;
use App\Models\Like;
use Illuminate\Support\Facades\Log;
use App\Models\Recommendation;
use App\Models\PriceTag;

class TopController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $likedGalleries = [];
        $page = $request->get('page', 1);
        $priceTags = PriceTag::all();
        $displayedIds = $request->ajax()
            ? array_map('intval', array_filter(explode(',', $request->get('displayed_ids', ''))))
            : [];
        if ($user) {
            $recommendedPosts = $this->getRecommendeds($user, $page, $displayedIds);
            $likedGalleries = Like::where('user_id', $user->id)
            ->pluck('car_gallery_id')
            ->toArray();
        } else {
            $recommendedPosts = $this->getRandoms($page, $displayedIds);
        }
        if ($request->filled('query')) {
            $query = $request->input('query');
            $searchResults = CarGallery::where('title', 'LIKE', "%{$query}%")
                ->orWhereHas('tags', function ($tagQuery) use ($query) {
                    $tagQuery->where('tags.name', 'LIKE', "%{$query}%");
                })
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
            Log::info('Infinite scroll IDs:', $recommendedPosts->pluck('id')->toArray());
            return response()->json($recommendedPosts);
        }
        Log::info('初期表示投稿ID:', $recommendedPosts->pluck('id')->toArray());
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

        if (empty($topTags)) {
            return $this->getRandoms($page, $displayedIds);
        }
        //四つのタグのカウント合計
        $totalCount = array_sum($topTags);
        $tagPostCounts = [];
        //投稿数の比率計算
        foreach ($topTags as $tagId => $count) {
            $postCountForTag = (int) (($count / $totalCount) * 12);
            $tagPostCounts[$tagId] = max($postCountForTag, 1);
            Log::info('Tag ID: ' . $tagId . ', Post Count: ' . $postCountForTag);
        }

        $recommendedPosts = collect();
        foreach ($tagPostCounts as $tagId => $postCount) {
            $existingIds = $recommendedPosts->pluck('id')->toArray();
            $excludeIds = array_merge($existingIds, $displayedIds);
            $postsForTag = CarGallery::whereHas('tags', function ($query) use ($tagId) {
                    $query->where('tags.id', $tagId);
                })
                ->whereNotIn('id', $excludeIds)
                ->withCount('likes')
                ->limit($postCount)
                ->get();
            Log::info($postsForTag->pluck('id'));
            $recommendedPosts = $recommendedPosts->merge($postsForTag);
        }
        $recommendedPosts = $recommendedPosts->unique('id');
        if ($recommendedPosts->count() < 12) {
            $existingPostIds = $recommendedPosts->pluck('id')->toArray();
            $remainingCount = 12 - $recommendedPosts->count();
            $randomPosts = CarGallery::whereNotIn('id', array_merge($existingPostIds, $displayedIds))
                ->withCount('likes')
                ->orderByDesc('id')
                ->limit($remainingCount)
                ->get();
            Log::info($randomPosts->pluck('id'));
            $recommendedPosts = $recommendedPosts->merge($randomPosts);
        }
        return $recommendedPosts->shuffle();
    }

    private function getRandoms($page = 1, $displayedIds = [])
    {
        return CarGallery::withCount('likes')
            ->whereNotIn('id', $displayedIds)
            ->skip(($page - 1) * 12)
            ->limit(12)
            ->get();
    }

    public function store(Request $request)
    {
        $userId = Auth::id();
        $request->validate([
            'title' => 'required|max:255',
            'image_path' => 'nullable|image|max:2048',
            'price_tag_id' => 'required|exists:price_tags,id',
            'tags' => 'nullable|string',
        ],[
            'title.required' => '名前を入力してください。',
            'title.string' => '名前は文字列で入力してください。',
            'image_path.image' => '画像形式でアップロードしてください。',
            'image_path.max' => '画像は2MB以内でアップロードしてください。',
            'price_tag_id.required' => '値段を選択してください。',
        ]);
        $imagePath = $request->file('image')->store('images', 'public');
        $gallery = CarGallery::create([
            'user_id' => $userId,
            'title' => $request->title,
            'image_path' => $imagePath,
            'price_tag_id' => $request->price_tag_id,
        ]);
        if ($request->filled('tags')) {
            $tagNames = explode(',', $request->tags);

            foreach ($tagNames as $tagName) {
                $cleanTagName = trim(mb_convert_kana($tagName, "as"));

                $tag = Tag::firstOrCreate(['name' => $cleanTagName]);

                $gallery->tags()->attach($tag->id);
            }
        }
        return redirect()->route('top.index');
    }
}

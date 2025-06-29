<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CarGallery;
use App\Models\Tag;
use App\Models\Recommendation;
use App\Models\Like;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
    public function Like(Request $request)
    {
        $user = Auth::user();
        $galleryId = $request->input('car_gallery_id');

        $like = Like::firstWhere([
                'user_id' => $user->id,
                'car_gallery_id' => $galleryId
            ]);

        if ($like) {
            $this->destoreRecommendations($user->id, $galleryId);
            $like->delete();
            $liked = false;
        } else {
            Like::create([
                'user_id' => $user->id,
                'car_gallery_id' => $galleryId,
            ]);
            $this->storeRecommendations($user->id, $galleryId);
            $liked = true;
        }

        $likesCount = Like::where('car_gallery_id', $galleryId)->count();


        return response()->json([
            'liked' => $liked,
            'likes_count' => $likesCount,
        ]);

    }

    private function storeRecommendations($userId, $galleryId)
    {
        $tagIds = CarGallery::findOrFail($galleryId)
                ->tags()
                ->pluck('tags.id');
        $recommendations = Recommendation::where('user_id', $userId)
            ->whereIn('tag_id', $tagIds)
            ->get()
            ->keyBy('tag_id');
        foreach ($tagIds as $tagId) {
            $recommendation = $recommendations->get($tagId);
            if ($recommendation) {
                $recommendation->increment('count');
            } else {
                Recommendation::create([
                    'user_id' => $userId,
                    'tag_id' => $tagId,
                    'count' => 1,
                ]);
            }
        }
    }

    private function destoreRecommendations($userId, $galleryId)
    {
        $tagIds = CarGallery::findOrFail($galleryId)
                ->tags()
                ->pluck('tags.id');
        $recommendations = Recommendation::where('user_id', $userId)
                ->whereIn('tag_id', $tagIds)
                ->get()
                ->keyBy('tag_id');

        foreach ($tagIds as $tagId) {
            $recommendation = $recommendations->get($tagId);
            if ($recommendation) {
                $recommendation->decrement('count');
                if ($recommendation->count == 0) {
                    $recommendation->delete();
                }
            }
        }
    }
}

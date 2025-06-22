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

        $like = Like::where('user_id', $user->id)
                    ->where('car_gallery_id', $galleryId)
                    ->first();

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
        $gallery = CarGallery::find($galleryId);
        $tags = $gallery->tags;
        foreach ($tags as $tag) {
            $recommendation = Recommendation::where('user_id', $userId)
                ->where('tag_id', $tag->id)
                ->first();

            if ($recommendation) {
                $recommendation->increment('count');
            } else {
                Recommendation::create([
                    'user_id' => $userId,
                    'tag_id' => $tag->id,
                    'count' => 1,
                ]);
            }
        }
    }

    private function destoreRecommendations($userId, $galleryId)
    {
        $gallery = CarGallery::find($galleryId);
        $tags = $gallery->tags;

        foreach ($tags as $tag) {
            $recommendation = Recommendation::where('user_id', $userId)
                ->where('tag_id', $tag->id)
                ->first();
            if ($recommendation) {
                // カウントを減らす
                $recommendation->decrement('count');
                // カウントがゼロになったら削除する
                if ($recommendation->count == 0) {
                    $recommendation->delete();
                }
            }
        }
    }
}

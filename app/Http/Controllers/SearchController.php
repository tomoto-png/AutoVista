<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CarGallery;
use App\Models\Tag;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('query');
        if (!$query) {
            return response()->json([]);
        }
        $tags = Tag::where('name', 'LIKE', "%{$query}%")
                ->limit(10)
                ->get();
        return response()->json($tags);
    }
    public function suggestions(Request $request)
    {
        $query = $request->input('q');

        // ギャラリーのサジェストを取得
        $gallery = CarGallery::where('title', 'LIKE', "%{$query}%")
            ->limit(5)
            ->get(['title as name']);

        // タグのサジェストを取得
        $tags = Tag::where('name', 'LIKE', "%{$query}%")
            ->limit(5)
            ->get(['name']);

        // ギャラリーとタグを結合
        $uniqueSuggestions = $gallery->concat($tags)->unique('name')->values();

        return response()->json($uniqueSuggestions);
    }
}

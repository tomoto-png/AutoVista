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
        $tags = Tag::where('name', 'LIKE', "%{$query}%")->limit(10)->get();
        return response()->json($tags);
    }
    public function suggestions(Request $request)
    {
        $query = $request->input('q');

        // ギャラリーのサジェストを取得
        $gallerySuggestions = CarGallery::where('title', 'LIKE', "%{$query}%")
            ->limit(5)
            ->get(['title as name']);

        // タグのサジェストを取得
        $tagSuggestions = Tag::where('name', 'LIKE', "%{$query}%")
            ->limit(5)
            ->get(['name']);

        // ギャラリーとタグのサジェストを結合
        $suggestions = $gallerySuggestions->concat($tagSuggestions);

        // 重複を排除
        $uniqueSuggestions = $suggestions->unique('name');

        return response()->json($uniqueSuggestions);
    }
}

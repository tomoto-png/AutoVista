<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CarGallery;
use App\Models\Tag;
use Illuminate\Support\Facades\Log;

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

        $gallerySuggestions = CarGallery::where('title', 'LIKE', "%{$query}%")
            ->limit(5)
            ->get(['title as name']);
        Log::info('Gallery Suggestions:', $gallerySuggestions->toArray());

        $tagSuggestions = Tag::where('name', 'LIKE', "%{$query}%")
            ->limit(5)
            ->get(['name']);
        Log::info('Tag Suggestions:', $tagSuggestions->toArray());

        $suggestions = $gallerySuggestions->concat($tagSuggestions);
        log::info('Suggestions:', $suggestions->toArray());
        return response()->json($suggestions);
    }
}

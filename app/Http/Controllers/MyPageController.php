<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CarGallery;
use App\Models\Like;
use App\Models\Tag;
use App\Models\PriceTag;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class MyPageController extends Controller
{
    public function index(){
        $user = Auth::user();
        $likedGalleries = [];
        $priceTags = PriceTag::all();
        $userPosts = CarGallery::where('user_id', $user->id)
            ->withCount('likes')
            ->orderByDesc('created_at')
            ->paginate(12);
        $likedPosts = CarGallery::whereHas('likes', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->withCount('likes')
            ->orderByDesc('created_at')
            ->paginate(12);
        if (request()->ajax()) {
            return response()->json([
                'userPosts' => $userPosts,
                'likedPosts' => $likedPosts,
            ]);
        }

        $likedGalleries = Like::where('user_id', $user->id)
            ->pluck('car_gallery_id')
            ->toArray();
        return view('mypage.index', compact('user', 'userPosts', 'likedPosts','likedGalleries', 'priceTags'));
    }
    public function edit(){
        $user = Auth::user();
        return view('mypage.edit', compact('user'));
    }
    public function update(Request $request){
        $user = Auth::user();
        $request->validate([
            'name' => 'required|string|max:255',
            'text' => 'required|string|max:500',
            'avatar' => 'nullable|image|max:2048',
        ],[
            'name.string' => '名前は文字列で入力してください。',
            'text.string' => '自己紹介文は文字列で入力してください。',
            'text.max' => '自己紹介文は500文字以内で入力してください。',
            'avatar.image' => '画像形式でアップロードしてください。',
            'avatar.max' => '画像は2MB以内でアップロードしてください。',
        ]);
        $avatar = $request->file('avatar') ? $request->file('avatar')->store('images', 'public') : $user->avatar;
        $user->update([
            'name' => $request->input('name'),
            'text' => $request->input('text'),
            'avatar' => $avatar,
        ]);
        return redirect()->route('mypage.index');
    }
    public function destroy($id)
    {
        $gallery = CarGallery::findOrFail($id);
        if ($gallery->image_path) {
            Storage::disk('public')->delete($gallery->image_path);
        }
        $gallery->tags()->detach();
        $gallery->delete();
        return redirect()->route('mypage.index');
    }
    public function editGallery($id)
    {
        $carGallery = CarGallery::with(['tags', 'priceTag'])->findOrFail($id);
        Log::debug("出力内容");
        return response()->json($carGallery);
    }

    public function updateGgallery(Request $request, $id)
    {
        $carGallery = CarGallery::findOrFail($id);
        Log::debug("更新内容");
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
        $imagePath = $carGallery->image_path;

        if ($request->hasFile('image')) {
            if ($carGallery->image_path) {
                Storage::disk('public')->delete($carGallery->image_path);
            }
            Log::debug('画像がアップロードされました: ');
            $imagePath = $request->file('image')->store('images', 'public');
        } else {
            Log::debug('画像がアップロードされませんでした: ');
        }

        $carGallery->update([
            'title' => $request->title,
            'image_path' => $imagePath,
            'price_tag_id' => $request->price_tag_id,
        ]);
        if ($request->filled('tags')) {
            $tagNames = explode(',', $request->tags);
            $tagIds = [];

            foreach ($tagNames as $tagName) {
                $cleanTagName = trim(mb_convert_kana($tagName, "as"));
                $tag = Tag::firstOrCreate(['name' => $cleanTagName]);
                $tagIds[] = $tag->id;
            }
            $carGallery->tags()->sync($tagIds);
        } else {
            $carGallery->tags()->detach();
        }

        return redirect()->route('mypage.index');
    }
}

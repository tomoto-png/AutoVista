<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\CarGallery;
use App\Models\Like;
use App\Models\Tag;
use App\Models\PriceTag;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class MyPageController extends Controller
{
    public function index(){
        $user = Auth::user();
        $priceTags = PriceTag::all();
        $type = request()->query('type');
        $userPosts = CarGallery::where('user_id', $user->id)
            ->withCount('likes')
            ->latest()
            ->paginate(12);
        $likedPosts = CarGallery::whereHas('likes', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with(['tags'])
            ->withCount('likes')
            ->latest()
            ->paginate(12);
        if (request()->ajax()) {
            return response()->json([
                'userPosts' => $userPosts,
                'likedPosts' => $likedPosts,
            ]);
        }
        return view('mypage.index', compact('user', 'userPosts', 'likedPosts', 'priceTags'));
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
        try {
            DB::transaction(function () use ($user, $request) {
                $avatar = $user->avatar;
                if ($request->hasFile('avatar')) {
                    if ($user->avatar) {
                        Storage::disk('public')->delete($user->avatar);
                    }
                    $avatar = $request->file('avatar')->store('images', 'public');
                }
                $user->update([
                    'name' => $request->input('name'),
                    'text' => $request->input('text'),
                    'avatar' => $avatar,
                ]);
            });
        } catch (\Exception $e) {
            throw $e;
        }
        return redirect()->route('mypage.index');
    }
    public function destroy($id)
    {
        $gallery = CarGallery::findOrFail($id);
        try{
            DB::transaction(function () use ($id, $gallery) {
                if ($gallery->image_path) {
                    Storage::disk('public')->delete($gallery->image_path);
                    $gallery->tags()->detach();
                    $gallery->delete();
                }
            });
        } catch (\Exception $e) {
            throw $e;
        }

        return redirect()->route('mypage.index');
    }
    public function editGallery($id)
    {
        $carGallery = CarGallery::with(['tags', 'priceTag'])
                        ->findOrFail($id);
        return response()->json($carGallery);
    }

    public function updateGgallery(Request $request, $id)
    {
        $carGallery = CarGallery::findOrFail($id);
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
        try {
            DB::transaction(function () use ($request, $carGallery, $imagePath) {
                if ($request->hasFile('image')) {
                    if ($carGallery->image_path) {
                        Storage::disk('public')->delete($carGallery->image_path);
                    }
                    $imagePath = $request->file('image')->store('images', 'public');
                }

                $carGallery->update([
                    'title' => $request->title,
                    'image_path' => $imagePath,
                    'price_tag_id' => $request->price_tag_id,
                ]);

                if ($request->filled('tags')) {
                    $tagNames = array_filter(array_map(function($tagName) {
                        return trim(mb_convert_kana($tagName, "as"));
                    }, explode(',', $request->tags)));

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
                    $carGallery->tags()->sync($allTags->pluck('id')->all());
                } else {
                    $carGallery->tags()->detach();
                }
            });
        } catch (\Exception $e) {
            throw $e;
        }

        return redirect()->route('mypage.index');
    }
}

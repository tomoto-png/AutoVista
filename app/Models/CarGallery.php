<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
class CarGallery extends Model
{
    protected $fillable = ['user_id','title','image_path','price_tag_id'];
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'car_gallery_tags', 'car_gallery_id', 'tag_id');
    }
    public function priceTag(): BelongsTo
    {
        return $this->belongsTo(PriceTag::class);
    }
}

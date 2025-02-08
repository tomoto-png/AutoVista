<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Tag extends Model
{
    protected $fillable = ['name'];

    public function carGalleries(): BelongsToMany
    {
        return $this->belongsToMany(CarGallery::class, 'car_gallery_tags', 'tag_id', 'car_gallery_id');
    }
    public function recommendations(): HasMany
    {
        return $this->hasMany(Recommendation::class);
    }
}

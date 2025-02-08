<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class GalleryTag extends Model
{
    protected $fillable = ['car_gallery_id', 'tag_id'];

    public function carGallery()
    {
        return $this->belongsTo(Post::class, 'car_gallery_id');
    }

    public function tag()
    {
        return $this->belongsTo(Tag::class, 'tag_id');
    }
}

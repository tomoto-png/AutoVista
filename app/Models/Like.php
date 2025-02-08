<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Like extends Model
{
    protected $fillable = ['car_gallery_id','user_id'];

    public function carGallery(): BelongsTo
    {
        return $this->belongsTo(CarGallery::class);
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

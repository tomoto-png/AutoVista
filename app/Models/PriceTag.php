<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class PriceTag extends Model
{
    public function carGalleries(): HasMany
    {
        return $this->hasMany(CarGallery::class);
    }
}

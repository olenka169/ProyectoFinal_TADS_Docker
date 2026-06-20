<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BrandModel extends Model
{
    protected $fillable = [
        'brand_id',
        'name',
        'code',
        'description'
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
}

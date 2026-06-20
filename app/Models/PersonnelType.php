<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonnelType extends Model
{
    protected $fillable = [
        'name',
        'description'
    ];

    public function personnels(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Personnel::class);
    }
}

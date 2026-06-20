<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    protected $fillable = [
        'date',
        'description',
        'status'
    ];

    protected $casts = [
        'date' => 'date',
        'status' => 'boolean',
    ];
}
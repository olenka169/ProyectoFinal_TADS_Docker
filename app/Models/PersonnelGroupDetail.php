<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonnelGroupDetail extends Model
{
    protected $fillable = [
        'personnel_group_id',
        'personnel_id'
    ];

    public function group()
    {
        return $this->belongsTo(
            PersonnelGroup::class,
            'personnel_group_id'
        );
    }

    public function personnel()
    {
        return $this->belongsTo(
            Personnel::class
        );
    }
}
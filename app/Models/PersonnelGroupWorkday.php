<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonnelGroupWorkday extends Model
{
    protected $fillable = [
        'personnel_group_id',
        'day'
    ];

    public function group()
    {
        return $this->belongsTo(PersonnelGroup::class);
    }
}
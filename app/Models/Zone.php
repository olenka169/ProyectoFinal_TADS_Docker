<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    protected $fillable = [
        'name',
        'department_id',
        'province_id',
        'district_id',
        'description',
        'average_waste',
        'status',
        'coordinates',
    ];

    protected $casts = [
        'coordinates' => 'array',
        'status' => 'boolean',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function personnelGroups()
    {
        return $this->hasMany(PersonnelGroup::class);
    }
}
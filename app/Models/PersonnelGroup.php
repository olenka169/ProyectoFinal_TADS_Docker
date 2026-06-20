<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonnelGroup extends Model
{
    protected $fillable = [
        'name',
        'zone_id',
        'shift_id',
        'vehicle_id',
        'driver_id',
        'status'
    ];

    protected $casts = [
        'status' => 'boolean'
    ];

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver()
    {
        return $this->belongsTo(
            Personnel::class,
            'driver_id'
        );
    }

    public function helpers()
    {
        return $this->hasMany(
            PersonnelGroupDetail::class
        );
    }

    public function workdays()
    {
        return $this->hasMany(PersonnelGroupWorkday::class);
    }
}
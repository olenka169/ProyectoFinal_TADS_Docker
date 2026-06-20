<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Schedule extends Model
{
    protected $fillable = [
        'personnel_group_id',
        'zone_id',
        'shift_id',
        'vehicle_id',
        'driver_id',
        'start_date',
        'end_date',
        'status',
        'notes'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function personnelGroup(): BelongsTo
    {
        return $this->belongsTo(PersonnelGroup::class);
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Personnel::class, 'driver_id');
    }

    public function helpers(): BelongsToMany
    {
        return $this->belongsToMany(Personnel::class, 'schedule_helpers', 'schedule_id', 'personnel_id');
    }

    public function workdays(): HasMany
    {
        return $this->hasMany(ScheduleWorkday::class);
    }

    public function dailies(): HasMany
    {
        return $this->hasMany(ScheduleDaily::class);
    }
}

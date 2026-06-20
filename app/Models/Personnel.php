<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Personnel extends Model
{
    protected $fillable = [
        'dni',
        'personnel_type_id',
        'names',
        'lastnames',
        'birthdate',
        'phone',
        'email',
        'status',
        'password',
        'address',
        'photo_path',
        'license_number',
    ];

    protected $hidden = ['password'];

    public function type(): BelongsTo
    {
        return $this->belongsTo(PersonnelType::class, 'personnel_type_id');
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function activeContract()
    {
        return $this->hasOne(Contract::class)->where('is_active', true)->latest();
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function vacations(): HasMany
    {
        return $this->hasMany(Vacation::class);
    }

    public function driverGroups()
    {
        return $this->hasMany(PersonnelGroup::class,'driver_id');
    }

    public function helperGroups()
    {
        return $this->hasMany(PersonnelGroupDetail::class);
    }
}

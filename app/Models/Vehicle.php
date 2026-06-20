<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'brand_model_id',
        'vehicle_type_id',
        'vehicle_color_id',
        'plate',
        'year',
        'load_capacity',
        'fuel_capacity',
        'compaction_capacity',
        'passenger_capacity',
        'description',
        'engine_number',
        'chassis_number',
        'mileage',
        'status'
    ];

    public function model(): BelongsTo
    {
        return $this->belongsTo(BrandModel::class, 'brand_model_id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(VehicleType::class, 'vehicle_type_id');
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(VehicleColor::class, 'vehicle_color_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(VehicleImage::class);
    }
    
    public function personnelGroups()
    {
        return $this->hasMany(PersonnelGroup::class);
    }
}

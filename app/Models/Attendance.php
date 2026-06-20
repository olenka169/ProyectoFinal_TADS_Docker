<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Shift;

class Attendance extends Model
{
    protected $fillable = [
        'personnel_id',
        'date',
        'time',
        'shift_id',
        'type',
        'status',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function personnel(): BelongsTo
    {
        return $this->belongsTo(Personnel::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }
}
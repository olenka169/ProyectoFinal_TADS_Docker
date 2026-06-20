<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vacation extends Model
{
    use HasFactory;

    protected $fillable = [
        'personnel_id',
        'start_date',
        'end_date',
        'requested_days',
        'status',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function personnel(): BelongsTo
    {
        return $this->belongsTo(Personnel::class);
    }

    /**
     * Calcula los días de vacaciones usados por un personal en un año específico.
     */
    public static function getUsedDays(int $personnelId, int $year): int
    {
        return self::where('personnel_id', $personnelId)
            ->where('status', 'Aprobada')
            ->whereYear('start_date', $year)
            ->sum('requested_days');
    }

    /**
     * Calcula los días disponibles (asumiendo 30 por año).
     */
    public static function getAvailableDays(int $personnelId, int $year): int
    {
        $maxDays = 30;
        $usedDays = self::getUsedDays($personnelId, $year);
        return max(0, $maxDays - $usedDays);
    }
}

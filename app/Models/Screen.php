<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Screen extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'emplacement',
        'sports_hall_id',
        'localisation',
        'device_key',
        'status',
    ];

    public function playlistAssignments(): HasMany
    {
        return $this->hasMany(ScreenPlaylist::class)->orderByDesc('starts_at');
    }

    public function sportsHall(): BelongsTo
    {
        return $this->belongsTo(SportsHall::class);
    }

    public function adSchedules(): HasMany
    {
        return $this->hasMany(AdSchedule::class);
    }

    public function programs(): HasMany
    {
        return $this->hasMany(Program::class);
    }
}

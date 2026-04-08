<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'screen_id',
        'media_id',
        'starts_at',
        'ends_at',
        'display_every_loops',
        'duration_override',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'display_every_loops' => 'integer',
            'duration_override' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function screen(): BelongsTo
    {
        return $this->belongsTo(Screen::class);
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }
}

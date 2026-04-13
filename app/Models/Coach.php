<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Coach extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'first_name',
        'email',
        'specialty',
        'sports_hall_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sports_hall_id' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function sportsHall(): BelongsTo
    {
        return $this->belongsTo(SportsHall::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Playlist extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(PlaylistItem::class)->orderBy('order');
    }

    public function screens(): BelongsToMany
    {
        return $this->belongsToMany(Screen::class, 'screen_playlists')
            ->withPivot(['is_active', 'starts_at', 'ends_at'])
            ->withTimestamps();
    }
}

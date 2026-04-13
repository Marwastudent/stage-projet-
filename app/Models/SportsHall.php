<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SportsHall extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'matricule',
        'localisation',
        'maps_url',
    ];

    public function screens(): HasMany
    {
        return $this->hasMany(Screen::class);
    }

    public function coaches(): HasMany
    {
        return $this->hasMany(Coach::class);
    }
}

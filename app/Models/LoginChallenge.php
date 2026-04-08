<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class LoginChallenge extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'challenge_token',
        'otp_hash',
        'device_name',
        'attempts',
        'last_sent_at',
        'expires_at',
        'consumed_at',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'last_sent_at' => 'datetime',
            'expires_at' => 'datetime',
            'consumed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return ! ($this->expires_at instanceof Carbon) || $this->expires_at->isPast();
    }

    public function isConsumed(): bool
    {
        return $this->consumed_at instanceof Carbon;
    }

    public function matchesOtp(string $otp): bool
    {
        $hash = hash('sha256', $otp.'|'.config('app.key'));

        return hash_equals($this->otp_hash, $hash);
    }
}


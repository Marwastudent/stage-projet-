<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Program extends Model
{
    use HasFactory;

    public const DAYS = [
        'lundi',
        'mardi',
        'mercredi',
        'jeudi',
        'vendredi',
        'samedi',
        'dimanche',
    ];

    protected $fillable = [
        'title',
        'course_type',
        'day',
        'start_time',
        'end_time',
        'duration',
        'coach',
        'room',
        'screen_id',
        'display_order',
        'is_active',
    ];

    protected $appends = [
        'computed_end_time',
    ];

    protected function casts(): array
    {
        return [
            'duration' => 'integer',
            'screen_id' => 'integer',
            'display_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function screen(): BelongsTo
    {
        return $this->belongsTo(Screen::class);
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when(($filters['day'] ?? null) !== null && $filters['day'] !== '', function (Builder $builder) use ($filters): void {
                $builder->where('day', $filters['day']);
            })
            ->when(($filters['coach'] ?? null) !== null && $filters['coach'] !== '', function (Builder $builder) use ($filters): void {
                $builder->where('coach', 'like', '%'.$filters['coach'].'%');
            })
            ->when(($filters['room'] ?? null) !== null && $filters['room'] !== '', function (Builder $builder) use ($filters): void {
                $builder->where('room', 'like', '%'.$filters['room'].'%');
            })
            ->when(($filters['screen_id'] ?? null) !== null && $filters['screen_id'] !== '', function (Builder $builder) use ($filters): void {
                $builder->where('screen_id', $filters['screen_id']);
            })
            ->when(($filters['is_active'] ?? null) !== null && $filters['is_active'] !== '', function (Builder $builder) use ($filters): void {
                $builder->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $filters['is_active']);
            });
    }

    public function scopeOrdered(Builder $query): Builder
    {
        $orderCase = collect(self::DAYS)
            ->map(fn (string $day, int $index): string => "when '{$day}' then ".($index + 1))
            ->implode(' ');

        return $query
            ->orderByRaw("case day {$orderCase} else 999 end")
            ->orderBy('start_time')
            ->orderBy('display_order');
    }

    public static function hasConflict(array $data, ?int $ignoreId = null): bool
    {
        if (
            empty($data['day'])
            || empty($data['start_time'])
            || empty($data['duration'])
            || empty($data['room'])
            || (array_key_exists('is_active', $data) && ! $data['is_active'])
        ) {
            return false;
        }

        $newStart = self::makeTime((string) $data['start_time']);
        $newEnd = (clone $newStart)->addMinutes((int) $data['duration']);

        $query = static::query()
            ->where('day', $data['day'])
            ->where('room', $data['room'])
            ->where('is_active', true);

        if ($ignoreId !== null) {
            $query->whereKeyNot($ignoreId);
        }

        return $query->get()->contains(function (Program $program) use ($newStart, $newEnd): bool {
            $existingStart = self::makeTime((string) $program->start_time);
            $existingEnd = (clone $existingStart)->addMinutes((int) $program->duration);

            return $newStart < $existingEnd && $newEnd > $existingStart;
        });
    }

    public function getComputedEndTimeAttribute(): ?string
    {
        if ($this->start_time === null || $this->duration === null) {
            return null;
        }

        return self::makeTime((string) $this->start_time)
            ->addMinutes((int) $this->duration)
            ->format('H:i');
    }

    private static function makeTime(string $time): Carbon
    {
        $normalized = strlen($time) === 5 ? $time : substr($time, 0, 8);
        $format = strlen($normalized) === 5 ? 'H:i' : 'H:i:s';

        return Carbon::createFromFormat($format, $normalized);
    }
}

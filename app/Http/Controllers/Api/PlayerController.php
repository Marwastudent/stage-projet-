<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdSchedule;
use App\Models\Screen;
use App\Models\ScreenPlaylist;
use Carbon\CarbonInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlayerController extends Controller
{
    public function feed(Request $request, string $device_key): JsonResponse
    {
        $screen = Screen::with('sportsHall')->where('device_key', $device_key)->first();

        if (! $screen) {
            return response()->json([
                'message' => 'Screen not found.',
            ], 404);
        }

        $serverClock = $this->makeServerClock();
        $planningData = $this->buildPlanningPayload($screen, $serverClock);

        if ($request->string('mode')->lower()->value() === 'planning') {
            return response()->json([
                'data' => $planningData,
            ]);
        }

        $now = now();

        $assignment = ScreenPlaylist::with(['playlist.items.media'])
            ->where('screen_id', $screen->id)
            ->where('is_active', true)
            ->where(function ($query) use ($now): void {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($query) use ($now): void {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            })
            ->orderByDesc('starts_at')
            ->first();

        $adSchedules = AdSchedule::with('media')
            ->where('screen_id', $screen->id)
            ->where('is_active', true)
            ->where('starts_at', '<=', $now)
            ->where('ends_at', '>=', $now)
            ->orderBy('starts_at')
            ->get();

        $ads = $adSchedules
            ->map(function (AdSchedule $schedule): ?array {
                $media = $schedule->media;

                if (! $media) {
                    return null;
                }

                $duration = $schedule->duration_override ?? $media->duration ?? ($media->type === 'image' ? 10 : 0);

                return [
                    'ad_schedule_id' => $schedule->id,
                    'name' => $schedule->name,
                    'media_id' => $media->id,
                    'title' => $media->title,
                    'type' => $media->type,
                    'file_path' => $media->file_path,
                    'url' => url('/storage/'.$media->file_path),
                    'duration' => (int) $duration,
                    'display_every_loops' => max(1, (int) $schedule->display_every_loops),
                    'starts_at' => $schedule->starts_at,
                    'ends_at' => $schedule->ends_at,
                ];
            })
            ->filter()
            ->values();

        if (! $assignment || ! $assignment->playlist) {
            if ($this->planningHasPrograms($planningData)) {
                return response()->json([
                    'data' => $planningData,
                ]);
            }

            return response()->json([
                'data' => [
                    'mode' => 'media',
                    'screen' => $screen,
                    'playlist' => null,
                    'items' => [],
                    'ads' => $ads,
                    'generated_at' => $serverClock['iso'],
                    'server_clock' => $serverClock,
                ],
            ]);
        }

        $playlist = $assignment->playlist;

        $items = $playlist->items
            ->map(function ($item): ?array {
                if (! $item->media) {
                    return null;
                }

                $duration = $item->duration_override ?? $item->media->duration ?? ($item->media->type === 'image' ? 10 : 0);

                return [
                    'playlist_item_id' => $item->id,
                    'media_id' => $item->media->id,
                    'title' => $item->media->title,
                    'type' => $item->media->type,
                    'file_path' => $item->media->file_path,
                    'url' => url('/storage/'.$item->media->file_path),
                    'duration' => (int) $duration,
                    'order' => $item->order,
                ];
            })
            ->filter()
            ->values();

        if ($items->isEmpty() && $this->planningHasPrograms($planningData)) {
            return response()->json([
                'data' => $planningData,
            ]);
        }

        return response()->json([
            'data' => [
                'mode' => 'media',
                'screen' => $screen,
                'playlist' => [
                    'id' => $playlist->id,
                    'name' => $playlist->name,
                ],
                'items' => $items,
                'ads' => $ads,
                'generated_at' => $serverClock['iso'],
                'server_clock' => $serverClock,
            ],
        ]);
    }

    public function show(string $device_key): View
    {
        return view('player', [
            'deviceKey' => $device_key,
        ]);
    }

    private function buildPlanningPayload(Screen $screen, array $serverClock): array
    {
        $programs = $screen->programs()
            ->where('is_active', true)
            ->ordered()
            ->get()
            ->map(function ($program): array {
                return [
                    'id' => $program->id,
                    'title' => $program->title,
                    'course_type' => $program->course_type,
                    'day' => $program->day,
                    'start_time' => substr((string) $program->start_time, 0, 5),
                    'end_time' => substr((string) $program->end_time, 0, 5),
                    'computed_end_time' => $program->computed_end_time,
                    'duration' => (int) $program->duration,
                    'coach' => $program->coach,
                    'room' => $program->room,
                    'display_order' => (int) $program->display_order,
                    'is_active' => (bool) $program->is_active,
                ];
            })
            ->values()
            ->all();

        return [
            'mode' => 'planning',
            'screen' => [
                'id' => $screen->id,
                'name' => $screen->name,
                'device_key' => $screen->device_key,
                'status' => $screen->status,
                'emplacement' => $screen->emplacement,
                'sports_hall' => $screen->sportsHall?->only(['id', 'name', 'localisation']),
            ],
            'programs' => $programs,
            'generated_at' => $serverClock['iso'],
            'server_clock' => $serverClock,
        ];
    }

    private function makeServerClock(): array
    {
        $timezone = (string) config('app.timezone', 'UTC');
        $serverNow = now()->setTimezone($timezone);

        return [
            'iso' => $serverNow->toIso8601String(),
            'timezone' => $timezone,
            'date' => $serverNow->toDateString(),
            'time' => $serverNow->format('H:i:s'),
            'day_key' => $this->dayKeyFromDate($serverNow),
        ];
    }

    private function dayKeyFromDate(CarbonInterface $date): string
    {
        return match ($date->dayOfWeekIso) {
            1 => 'lundi',
            2 => 'mardi',
            3 => 'mercredi',
            4 => 'jeudi',
            5 => 'vendredi',
            6 => 'samedi',
            default => 'dimanche',
        };
    }

    private function planningHasPrograms(array $planningData): bool
    {
        return ! empty($planningData['programs']);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdSchedule;
use App\Models\Screen;
use App\Models\ScreenPlaylist;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class PlayerController extends Controller
{
    public function feed(string $device_key): JsonResponse
    {
        $screen = Screen::with('sportsHall')->where('device_key', $device_key)->first();

        if (! $screen) {
            return response()->json([
                'message' => 'Screen not found.',
            ], 404);
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
            return response()->json([
                'data' => [
                    'screen' => $screen,
                    'playlist' => null,
                    'items' => [],
                    'ads' => $ads,
                    'generated_at' => now(),
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

        return response()->json([
            'data' => [
                'screen' => $screen,
                'playlist' => [
                    'id' => $playlist->id,
                    'name' => $playlist->name,
                ],
                'items' => $items,
                'ads' => $ads,
                'generated_at' => now(),
            ],
        ]);
    }

    public function show(string $device_key): View
    {
        return view('player', [
            'deviceKey' => $device_key,
        ]);
    }
}

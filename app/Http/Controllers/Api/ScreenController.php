<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Screen;
use App\Models\ScreenPlaylist;
use App\Models\SportsHall;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ScreenController extends Controller
{
    private const ALLOWED_STATUS = ['online', 'offline'];
    private const ALLOWED_EMPLACEMENTS = ['entree', 'sortie', 'cafeteria'];

    public function index(): JsonResponse
    {
        $screens = Screen::with('sportsHall')->orderBy('name')->paginate(20);

        $screens->through(function (Screen $screen): array {
            $assignment = $this->resolveCurrentAssignment($screen);

            return [
                'id' => $screen->id,
                'name' => $screen->name,
                'emplacement' => $screen->emplacement,
                'sports_hall_id' => $screen->sports_hall_id,
                'sports_hall' => $screen->sportsHall,
                'localisation' => $screen->sportsHall?->localisation ?? $screen->getAttribute('localisation'),
                'device_key' => $screen->device_key,
                'status' => $screen->status,
                'playlist' => $assignment?->playlist,
                'playlist_assignment' => $assignment,
                'created_at' => $screen->created_at,
                'updated_at' => $screen->updated_at,
            ];
        });

        return response()->json($screens);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'emplacement' => ['sometimes', 'nullable', 'in:'.implode(',', self::ALLOWED_EMPLACEMENTS)],
            'sports_hall_id' => ['required', 'exists:sports_halls,id'],
            'status' => ['nullable', 'in:'.implode(',', self::ALLOWED_STATUS)],
        ]);

        $sportsHall = SportsHall::findOrFail((int) $data['sports_hall_id']);

        $payload = [
            'name' => $data['name'],
            'sports_hall_id' => $sportsHall->id,
            'status' => $data['status'] ?? 'offline',
            'device_key' => $this->generateUniqueDeviceKey(),
        ];

        if (array_key_exists('emplacement', $data) && filled($data['emplacement'])) {
            $payload['emplacement'] = $data['emplacement'];
        }

        if (Schema::hasColumn('screens', 'localisation')) {
            $payload['localisation'] = $sportsHall->localisation;
        }

        $screen = Screen::create($payload);

        $screen->load('sportsHall');

        return response()->json([
            'message' => 'Screen created successfully.',
            'data' => $screen,
        ], 201);
    }

    public function show(Screen $screen): JsonResponse
    {
        $screen->load('sportsHall');

        $currentAssignment = $this->resolveCurrentAssignment($screen);
        $recentAssignments = $screen->playlistAssignments()->with('playlist')->limit(10)->get();

        return response()->json([
            'data' => [
                ...$screen->toArray(),
                'emplacement' => $screen->emplacement,
                'localisation' => $screen->sportsHall?->localisation ?? $screen->getAttribute('localisation'),
                'current_playlist' => $currentAssignment?->playlist,
                'current_playlist_assignment' => $currentAssignment,
                'recent_playlist_assignments' => $recentAssignments,
            ],
        ]);
    }

    public function update(Request $request, Screen $screen): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:120'],
            'emplacement' => ['sometimes', 'nullable', 'in:'.implode(',', self::ALLOWED_EMPLACEMENTS)],
            'sports_hall_id' => ['sometimes', 'required', 'exists:sports_halls,id'],
            'status' => ['sometimes', 'required', 'in:'.implode(',', self::ALLOWED_STATUS)],
        ]);

        if (array_key_exists('emplacement', $data) && blank($data['emplacement'])) {
            unset($data['emplacement']);
        }

        if (array_key_exists('sports_hall_id', $data) && Schema::hasColumn('screens', 'localisation')) {
            $sportsHall = SportsHall::find((int) $data['sports_hall_id']);
            $data['localisation'] = $sportsHall?->localisation ?? $screen->getAttribute('localisation');
        }

        $screen->update($data);

        return response()->json([
            'message' => 'Screen updated successfully.',
            'data' => $screen->fresh('sportsHall'),
        ]);
    }

    public function destroy(Screen $screen): JsonResponse
    {
        $screen->delete();

        return response()->json([
            'message' => 'Screen deleted successfully.',
        ]);
    }

    public function assignPlaylist(Request $request, Screen $screen): JsonResponse
    {
        $data = $request->validate([
            'playlist_id' => ['required', 'exists:playlists,id'],
            'is_active' => ['nullable', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);

        $isActive = $data['is_active'] ?? true;
        $startsAtRaw = $data['starts_at'] ?? null;
        $endsAtRaw = $data['ends_at'] ?? null;

        $startsAt = $startsAtRaw ? Carbon::parse($startsAtRaw) : now();
        $endsAt = $endsAtRaw ? Carbon::parse($endsAtRaw) : null;

        if ($startsAtRaw && !preg_match('/\d{1,2}:\d{2}/', (string) $startsAtRaw)) {
            $startsAt = $startsAt->startOfDay();
        }

        if ($endsAtRaw && !preg_match('/\d{1,2}:\d{2}/', (string) $endsAtRaw)) {
            $endsAt = $endsAt->endOfDay();
        }

        if ($isActive && $startsAt->lessThanOrEqualTo(now())) {
            ScreenPlaylist::where('screen_id', $screen->id)
                ->where('is_active', true)
                ->update(['is_active' => false]);
        }

        $assignment = ScreenPlaylist::create([
            'screen_id' => $screen->id,
            'playlist_id' => (int) $data['playlist_id'],
            'is_active' => $isActive,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
        ]);

        $assignment->load('playlist');

        return response()->json([
            'message' => 'Playlist assigned to screen successfully.',
            'data' => $assignment,
        ], 201);
    }

    private function resolveCurrentAssignment(Screen $screen): ?ScreenPlaylist
    {
        $now = now();

        return ScreenPlaylist::with('playlist')
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
    }

    private function generateUniqueDeviceKey(): string
    {
        do {
            $key = 'SCR-'.Str::upper(Str::random(10));
        } while (Screen::where('device_key', $key)->exists());

        return $key;
    }
}



<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ScreenPlaylist;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScreenPlaylistController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'screen_id' => ['nullable', 'integer', 'exists:screens,id'],
            'status' => ['nullable', 'in:running,planned,expired,inactive'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:200'],
        ]);

        $now = now();
        $query = ScreenPlaylist::query()
            ->with([
                'screen:id,name,device_key,status,sports_hall_id',
                'screen.sportsHall:id,name,matricule,localisation',
                'playlist:id,name',
            ])
            ->orderByDesc('starts_at')
            ->orderByDesc('id');

        if (! empty($filters['screen_id'])) {
            $query->where('screen_id', (int) $filters['screen_id']);
        }

        if (! empty($filters['status'])) {
            $status = $filters['status'];

            if ($status === 'inactive') {
                $query->where('is_active', false);
            }

            if ($status === 'running') {
                $query->where('is_active', true)
                    ->where(function ($q) use ($now): void {
                        $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
                    })
                    ->where(function ($q) use ($now): void {
                        $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
                    });
            }

            if ($status === 'planned') {
                $query->where('is_active', true)
                    ->whereNotNull('starts_at')
                    ->where('starts_at', '>', $now);
            }

            if ($status === 'expired') {
                $query->where('is_active', true)
                    ->whereNotNull('ends_at')
                    ->where('ends_at', '<', $now);
            }
        }

        $perPage = (int) ($filters['per_page'] ?? 50);
        $paginator = $query->paginate($perPage);

        $paginator->through(function (ScreenPlaylist $assignment) use ($now): array {
            $startsAt = $assignment->starts_at;
            $endsAt = $assignment->ends_at;

            $runtimeStatus = 'running';
            if (! $assignment->is_active) {
                $runtimeStatus = 'inactive';
            } elseif ($startsAt && $startsAt->greaterThan($now)) {
                $runtimeStatus = 'planned';
            } elseif ($endsAt && $endsAt->lessThan($now)) {
                $runtimeStatus = 'expired';
            }

            return [
                'id' => $assignment->id,
                'screen_id' => $assignment->screen_id,
                'playlist_id' => $assignment->playlist_id,
                'is_active' => $assignment->is_active,
                'starts_at' => $assignment->starts_at,
                'ends_at' => $assignment->ends_at,
                'runtime_status' => $runtimeStatus,
                'screen' => $assignment->screen,
                'playlist' => $assignment->playlist,
                'created_at' => $assignment->created_at,
                'updated_at' => $assignment->updated_at,
            ];
        });

        return response()->json($paginator);
    }

    public function destroy(ScreenPlaylist $screenPlaylist): JsonResponse
    {
        $screenPlaylist->delete();

        return response()->json([
            'message' => 'Playlist assignment deleted successfully.',
        ]);
    }

    public function update(Request $request, ScreenPlaylist $screenPlaylist): JsonResponse
    {
        $data = $request->validate([
            'screen_id' => ['sometimes', 'required', 'integer', 'exists:screens,id'],
            'playlist_id' => ['sometimes', 'required', 'integer', 'exists:playlists,id'],
            'is_active' => ['sometimes', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
        ]);

        $payload = [];

        if (array_key_exists('screen_id', $data)) {
            $payload['screen_id'] = (int) $data['screen_id'];
        }

        if (array_key_exists('playlist_id', $data)) {
            $payload['playlist_id'] = (int) $data['playlist_id'];
        }

        if (array_key_exists('is_active', $data)) {
            $payload['is_active'] = (bool) $data['is_active'];
        }

        if (array_key_exists('starts_at', $data)) {
            $payload['starts_at'] = $data['starts_at'] ? Carbon::parse($data['starts_at']) : null;
        }

        if (array_key_exists('ends_at', $data)) {
            $payload['ends_at'] = $data['ends_at'] ? Carbon::parse($data['ends_at']) : null;
        }

        $effectiveStartsAt = array_key_exists('starts_at', $payload) ? $payload['starts_at'] : $screenPlaylist->starts_at;
        $effectiveEndsAt = array_key_exists('ends_at', $payload) ? $payload['ends_at'] : $screenPlaylist->ends_at;
        $effectiveIsActive = array_key_exists('is_active', $payload) ? $payload['is_active'] : $screenPlaylist->is_active;
        $effectiveScreenId = array_key_exists('screen_id', $payload) ? $payload['screen_id'] : $screenPlaylist->screen_id;

        if ($effectiveStartsAt && $effectiveEndsAt && $effectiveEndsAt->lessThan($effectiveStartsAt)) {
            return response()->json([
                'message' => 'La date/heure de fin doit etre egale ou apres le debut.',
            ], 422);
        }

        if ($effectiveIsActive && (! $effectiveStartsAt || $effectiveStartsAt->lessThanOrEqualTo(now()))) {
            ScreenPlaylist::where('screen_id', $effectiveScreenId)
                ->where('id', '!=', $screenPlaylist->id)
                ->where('is_active', true)
                ->update(['is_active' => false]);
        }

        $screenPlaylist->update($payload);
        $screenPlaylist->load([
            'screen:id,name,device_key,status,sports_hall_id',
            'screen.sportsHall:id,name,matricule,localisation',
            'playlist:id,name',
        ]);

        return response()->json([
            'message' => 'Playlist assignment updated successfully.',
            'data' => $screenPlaylist,
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PlaylistItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlaylistItemController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = PlaylistItem::with(['media', 'playlist'])->orderBy('playlist_id')->orderBy('order');

        if ($request->filled('playlist_id')) {
            $query->where('playlist_id', $request->integer('playlist_id'));
        }

        return response()->json($query->paginate(50));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'playlist_id' => ['required', 'exists:playlists,id'],
            'media_id' => ['required', 'exists:media,id'],
            'order' => ['nullable', 'integer', 'min:1'],
            'duration_override' => ['nullable', 'integer', 'min:1'],
        ]);

        $playlistId = (int) $data['playlist_id'];
        $requestedOrder = isset($data['order']) ? (int) $data['order'] : null;

        $playlistItem = DB::transaction(function () use ($data, $playlistId, $requestedOrder): PlaylistItem {
            $maxOrder = (int) PlaylistItem::where('playlist_id', $playlistId)->max('order');

            $item = PlaylistItem::create([
                'playlist_id' => $playlistId,
                'media_id' => (int) $data['media_id'],
                'order' => $maxOrder + 1,
                'duration_override' => $data['duration_override'] ?? null,
            ]);

            if ($requestedOrder !== null) {
                $this->moveItemToPosition($item, $requestedOrder);
            }

            return $item->fresh(['media', 'playlist']);
        });

        return response()->json([
            'message' => 'Playlist item created successfully.',
            'data' => $playlistItem,
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $playlistItem = PlaylistItem::with(['media', 'playlist'])->findOrFail($id);

        return response()->json([
            'data' => $playlistItem,
        ]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $playlistItem = PlaylistItem::findOrFail($id);

        $data = $request->validate([
            'media_id' => ['sometimes', 'required', 'exists:media,id'],
            'order' => ['sometimes', 'required', 'integer', 'min:1'],
            'duration_override' => ['nullable', 'integer', 'min:1'],
        ]);

        DB::transaction(function () use ($playlistItem, $data): void {
            if (array_key_exists('media_id', $data)) {
                $playlistItem->media_id = (int) $data['media_id'];
            }

            if (array_key_exists('duration_override', $data)) {
                $playlistItem->duration_override = $data['duration_override'] !== null
                    ? (int) $data['duration_override']
                    : null;
            }

            $playlistItem->save();

            if (array_key_exists('order', $data)) {
                $this->moveItemToPosition($playlistItem->fresh(), (int) $data['order']);
            }
        });

        return response()->json([
            'message' => 'Playlist item updated successfully.',
            'data' => PlaylistItem::with(['media', 'playlist'])->findOrFail($id),
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $playlistItem = PlaylistItem::findOrFail($id);
        $playlistId = $playlistItem->playlist_id;

        DB::transaction(function () use ($playlistItem, $playlistId): void {
            $playlistItem->delete();
            $this->compactOrders($playlistId);
        });

        return response()->json([
            'message' => 'Playlist item deleted successfully.',
        ]);
    }

    private function moveItemToPosition(PlaylistItem $item, int $position): void
    {
        $playlistId = $item->playlist_id;

        $ids = PlaylistItem::where('playlist_id', $playlistId)
            ->orderBy('order')
            ->orderBy('id')
            ->pluck('id')
            ->all();

        $ids = array_values(array_filter($ids, static fn (int $id): bool => $id !== $item->id));

        $position = max(1, min($position, count($ids) + 1));

        array_splice($ids, $position - 1, 0, [$item->id]);

        $this->applyOrder($ids);
    }

    private function compactOrders(int $playlistId): void
    {
        $ids = PlaylistItem::where('playlist_id', $playlistId)
            ->orderBy('order')
            ->orderBy('id')
            ->pluck('id')
            ->all();

        $this->applyOrder($ids);
    }

    /**
     * Apply deterministic ordering while avoiding temporary unique collisions.
     */
    private function applyOrder(array $ids): void
    {
        $tempOrder = 100000;
        foreach ($ids as $id) {
            PlaylistItem::whereKey($id)->update(['order' => $tempOrder++]);
        }

        $order = 1;
        foreach ($ids as $id) {
            PlaylistItem::whereKey($id)->update(['order' => $order++]);
        }
    }
}

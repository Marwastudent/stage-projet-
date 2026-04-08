<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Playlist;
use App\Models\PlaylistItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PlaylistController extends Controller
{
    public function index(): JsonResponse
    {
        $playlists = Playlist::withCount('items')->orderBy('name')->paginate(20);

        return response()->json($playlists);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
        ]);

        $playlist = Playlist::create($data);

        return response()->json([
            'message' => 'Playlist created successfully.',
            'data' => $playlist,
        ], 201);
    }

    public function show(Playlist $playlist): JsonResponse
    {
        $playlist->load(['items.media']);

        return response()->json([
            'data' => $playlist,
        ]);
    }

    public function update(Request $request, Playlist $playlist): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:120'],
        ]);

        $playlist->update($data);

        return response()->json([
            'message' => 'Playlist updated successfully.',
            'data' => $playlist->fresh(),
        ]);
    }

    public function destroy(Playlist $playlist): JsonResponse
    {
        $playlist->delete();

        return response()->json([
            'message' => 'Playlist deleted successfully.',
        ]);
    }

    public function reorderItems(Request $request, Playlist $playlist): JsonResponse
    {
        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer', 'exists:playlist_items,id'],
            'items.*.order' => ['required', 'integer', 'min:1'],
        ]);

        $items = collect($data['items']);
        $itemIds = $items->pluck('id')->all();
        $totalItems = PlaylistItem::where('playlist_id', $playlist->id)->count();
        $ownedItemsCount = PlaylistItem::where('playlist_id', $playlist->id)->whereIn('id', $itemIds)->count();

        if (count($itemIds) !== $totalItems || $ownedItemsCount !== $totalItems) {
            throw ValidationException::withMessages([
                'items' => ['You must provide a full ordered list of items from this playlist.'],
            ]);
        }

        DB::transaction(function () use ($items): void {
            $sorted = $items->sortBy('order')->values();

            $tempOrder = 100000;
            foreach ($sorted as $entry) {
                PlaylistItem::whereKey($entry['id'])->update(['order' => $tempOrder++]);
            }

            $finalOrder = 1;
            foreach ($sorted as $entry) {
                PlaylistItem::whereKey($entry['id'])->update(['order' => $finalOrder++]);
            }
        });

        $playlist->load(['items.media']);

        return response()->json([
            'message' => 'Playlist items reordered successfully.',
            'data' => $playlist,
        ]);
    }
}

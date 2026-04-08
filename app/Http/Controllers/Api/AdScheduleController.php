<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdSchedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdScheduleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = AdSchedule::with(['screen.sportsHall', 'media'])->orderByDesc('starts_at');

        if ($request->filled('screen_id')) {
            $query->where('screen_id', $request->integer('screen_id'));
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN));
        }

        return response()->json($query->paginate(20));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'screen_id' => ['required', 'exists:screens,id'],
            'media_id' => ['required', 'exists:media,id'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'display_every_loops' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'duration_override' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $adSchedule = AdSchedule::create([
            ...$data,
            'display_every_loops' => $data['display_every_loops'] ?? 1,
            'is_active' => $data['is_active'] ?? true,
        ]);

        $adSchedule->load(['screen.sportsHall', 'media']);

        return response()->json([
            'message' => 'Ad schedule created successfully.',
            'data' => $adSchedule,
        ], 201);
    }

    public function show(AdSchedule $adSchedule): JsonResponse
    {
        $adSchedule->load(['screen.sportsHall', 'media']);

        return response()->json([
            'data' => $adSchedule,
        ]);
    }

    public function update(Request $request, AdSchedule $adSchedule): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:120'],
            'screen_id' => ['sometimes', 'required', 'exists:screens,id'],
            'media_id' => ['sometimes', 'required', 'exists:media,id'],
            'starts_at' => ['sometimes', 'required', 'date'],
            'ends_at' => ['sometimes', 'required', 'date', 'after:starts_at'],
            'display_every_loops' => ['sometimes', 'required', 'integer', 'min:1', 'max:1000'],
            'duration_override' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['sometimes', 'required', 'boolean'],
        ]);

        $adSchedule->update($data);

        return response()->json([
            'message' => 'Ad schedule updated successfully.',
            'data' => $adSchedule->fresh(['screen.sportsHall', 'media']),
        ]);
    }

    public function destroy(AdSchedule $adSchedule): JsonResponse
    {
        $adSchedule->delete();

        return response()->json([
            'message' => 'Ad schedule deleted successfully.',
        ]);
    }
}

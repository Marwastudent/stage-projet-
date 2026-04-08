<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SportsHall;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SportsHallController extends Controller
{
    public function index(): JsonResponse
    {
        $sportsHalls = SportsHall::withCount('screens')->orderBy('name')->paginate(20);

        return response()->json($sportsHalls);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'matricule' => ['required', 'string', 'max:80', 'unique:sports_halls,matricule'],
            'localisation' => ['required', 'string', 'max:255'],
        ]);

        $sportsHall = SportsHall::create($data);

        return response()->json([
            'message' => 'Sports hall created successfully.',
            'data' => $sportsHall,
        ], 201);
    }

    public function show(SportsHall $sportsHall): JsonResponse
    {
        $sportsHall->load('screens');

        return response()->json([
            'data' => $sportsHall,
        ]);
    }

    public function update(Request $request, SportsHall $sportsHall): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:120'],
            'matricule' => ['sometimes', 'required', 'string', 'max:80', Rule::unique('sports_halls', 'matricule')->ignore($sportsHall->id)],
            'localisation' => ['sometimes', 'required', 'string', 'max:255'],
        ]);

        $sportsHall->update($data);

        return response()->json([
            'message' => 'Sports hall updated successfully.',
            'data' => $sportsHall->fresh(),
        ]);
    }

    public function destroy(SportsHall $sportsHall): JsonResponse
    {
        $sportsHall->delete();

        return response()->json([
            'message' => 'Sports hall deleted successfully.',
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coach;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class CoachController extends Controller
{
    public function index(): JsonResponse
    {
        $supportsSportsHallAssignments = $this->supportsSportsHallAssignments();

        $coaches = Coach::query()
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->when($supportsSportsHallAssignments, fn ($query) => $query->with('sportsHall:id,name,matricule'))
            ->paginate(20)
            ->through(function (Coach $coach) use ($supportsSportsHallAssignments) {
                if (! $supportsSportsHallAssignments) {
                    $coach->setRelation('sportsHall', null);
                }

                return $coach;
            });

        return response()->json($coaches);
    }

    public function store(Request $request): JsonResponse
    {
        $supportsSportsHallAssignments = $this->supportsSportsHallAssignments();

        $rules = [
            'name' => ['required', 'string', 'max:120'],
            'first_name' => ['nullable', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:255', 'unique:coaches,email'],
            'specialty' => ['nullable', 'string', 'max:120'],
            'is_active' => ['nullable', 'boolean'],
        ];

        if ($supportsSportsHallAssignments) {
            $rules['sports_hall_id'] = ['nullable', 'integer', 'exists:sports_halls,id'];
        }

        $data = $request->validate($rules);

        $payload = [
            'name' => $data['name'],
            'first_name' => $data['first_name'] ?? null,
            'email' => $data['email'] ?? null,
            'specialty' => $data['specialty'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ];

        if ($supportsSportsHallAssignments) {
            $payload['sports_hall_id'] = $data['sports_hall_id'] ?? null;
        }

        $coach = Coach::create($payload);

        return response()->json([
            'message' => 'Coach created successfully.',
            'data' => $this->serializeCoach($coach),
        ], 201);
    }

    public function show(Coach $coach): JsonResponse
    {
        return response()->json([
            'data' => $this->serializeCoach($coach),
        ]);
    }

    public function update(Request $request, Coach $coach): JsonResponse
    {
        $supportsSportsHallAssignments = $this->supportsSportsHallAssignments();

        $rules = [
            'name' => ['sometimes', 'required', 'string', 'max:120'],
            'first_name' => ['sometimes', 'nullable', 'string', 'max:120'],
            'email' => ['sometimes', 'nullable', 'email', 'max:255', Rule::unique('coaches', 'email')->ignore($coach->id)],
            'specialty' => ['sometimes', 'nullable', 'string', 'max:120'],
            'is_active' => ['sometimes', 'required', 'boolean'],
        ];

        if ($supportsSportsHallAssignments) {
            $rules['sports_hall_id'] = ['sometimes', 'nullable', 'integer', 'exists:sports_halls,id'];
        }

        $data = $request->validate($rules);

        if (! $supportsSportsHallAssignments) {
            unset($data['sports_hall_id']);
        }

        $coach->update($data);

        return response()->json([
            'message' => 'Coach updated successfully.',
            'data' => $this->serializeCoach($coach),
        ]);
    }

    public function destroy(Coach $coach): JsonResponse
    {
        $coach->delete();

        return response()->json([
            'message' => 'Coach deleted successfully.',
        ]);
    }

    private function serializeCoach(Coach $coach): Coach
    {
        $coach = $coach->fresh();

        if ($this->supportsSportsHallAssignments()) {
            return $coach->load('sportsHall:id,name,matricule');
        }

        $coach->setRelation('sportsHall', null);

        return $coach;
    }

    private function supportsSportsHallAssignments(): bool
    {
        return Schema::hasTable('coaches') && Schema::hasColumn('coaches', 'sports_hall_id');
    }
}

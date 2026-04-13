<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coach;
use App\Models\SportsHall;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class SportsHallController extends Controller
{
    public function index(): JsonResponse
    {
        $supportsMapsUrl = $this->supportsMapsUrl();
        $supportsCoachAssignments = $this->supportsCoachAssignments();

        $sportsHalls = SportsHall::query()
            ->withCount('screens')
            ->orderBy('name')
            ->when($supportsCoachAssignments, function ($query) {
                $query
                    ->withCount('coaches')
                    ->with(['coaches:id,name,first_name,sports_hall_id,is_active']);
            })
            ->paginate(20)
            ->through(function (SportsHall $sportsHall) use ($supportsMapsUrl, $supportsCoachAssignments) {
                if (! $supportsMapsUrl) {
                    $sportsHall->maps_url = null;
                }

                if (! $supportsCoachAssignments) {
                    $sportsHall->setAttribute('coaches_count', 0);
                    $sportsHall->setRelation('coaches', collect());
                }

                return $sportsHall;
            });

        return response()->json($sportsHalls);
    }

    public function store(Request $request): JsonResponse
    {
        $supportsMapsUrl = $this->supportsMapsUrl();
        $supportsCoachAssignments = $this->supportsCoachAssignments();

        $rules = [
            'name' => ['required', 'string', 'max:120'],
            'matricule' => ['nullable', 'string', 'max:80', 'unique:sports_halls,matricule'],
            'localisation' => ['required', 'string', 'max:255'],
        ];

        if ($supportsMapsUrl) {
            $rules['maps_url'] = ['nullable', 'url', 'max:500'];
        }

        if ($supportsCoachAssignments) {
            $rules['coach_ids'] = ['nullable', 'array'];
            $rules['coach_ids.*'] = ['integer', 'exists:coaches,id'];
        }

        $data = $request->validate($rules);

        $payload = [
            'name' => $data['name'],
            'matricule' => ($data['matricule'] ?? null) ?: $this->generateUniqueMatricule($data['name']),
            'localisation' => $data['localisation'],
        ];

        if ($supportsMapsUrl) {
            $payload['maps_url'] = $data['maps_url'] ?? null;
        }

        $sportsHall = SportsHall::create($payload);

        if ($supportsCoachAssignments) {
            $this->syncCoaches($sportsHall, $data['coach_ids'] ?? []);
        }

        return response()->json([
            'message' => 'Sports hall created successfully.',
            'data' => $this->serializeSportsHall($sportsHall),
        ], 201);
    }

    public function show(SportsHall $sportsHall): JsonResponse
    {
        return response()->json([
            'data' => $this->serializeSportsHall($sportsHall, true),
        ]);
    }

    public function update(Request $request, SportsHall $sportsHall): JsonResponse
    {
        $supportsMapsUrl = $this->supportsMapsUrl();
        $supportsCoachAssignments = $this->supportsCoachAssignments();

        $rules = [
            'name' => ['sometimes', 'required', 'string', 'max:120'],
            'matricule' => ['nullable', 'string', 'max:80', Rule::unique('sports_halls', 'matricule')->ignore($sportsHall->id)],
            'localisation' => ['sometimes', 'required', 'string', 'max:255'],
        ];

        if ($supportsMapsUrl) {
            $rules['maps_url'] = ['sometimes', 'nullable', 'url', 'max:500'];
        }

        if ($supportsCoachAssignments) {
            $rules['coach_ids'] = ['nullable', 'array'];
            $rules['coach_ids.*'] = ['integer', 'exists:coaches,id'];
        }

        $data = $request->validate($rules);

        if (array_key_exists('name', $data) && ! array_key_exists('matricule', $data)) {
            $data['matricule'] = $this->generateUniqueMatricule($data['name'], $sportsHall->id);
        }

        if (array_key_exists('matricule', $data) && blank($data['matricule'])) {
            $referenceName = $data['name'] ?? $sportsHall->name;
            $data['matricule'] = $this->generateUniqueMatricule($referenceName, $sportsHall->id);
        }

        $coachIds = null;
        if ($supportsCoachAssignments && array_key_exists('coach_ids', $data)) {
            $coachIds = $data['coach_ids'] ?? [];
            unset($data['coach_ids']);
        }

        if (! $supportsMapsUrl) {
            unset($data['maps_url']);
        }

        $sportsHall->update($data);

        if ($supportsCoachAssignments && $coachIds !== null) {
            $this->syncCoaches($sportsHall, $coachIds);
        }

        return response()->json([
            'message' => 'Sports hall updated successfully.',
            'data' => $this->serializeSportsHall($sportsHall),
        ]);
    }

    public function destroy(SportsHall $sportsHall): JsonResponse
    {
        if ($this->supportsCoachAssignments()) {
            Coach::where('sports_hall_id', $sportsHall->id)->update(['sports_hall_id' => null]);
        }

        $sportsHall->delete();

        return response()->json([
            'message' => 'Sports hall deleted successfully.',
        ]);
    }

    private function syncCoaches(SportsHall $sportsHall, array $coachIds): void
    {
        if (! $this->supportsCoachAssignments()) {
            return;
        }

        $coachIds = collect($coachIds)
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        Coach::where('sports_hall_id', $sportsHall->id)
            ->when($coachIds !== [], fn ($query) => $query->whereNotIn('id', $coachIds))
            ->update(['sports_hall_id' => null]);

        if ($coachIds === []) {
            return;
        }

        Coach::whereIn('id', $coachIds)->update(['sports_hall_id' => $sportsHall->id]);
    }

    private function serializeSportsHall(SportsHall $sportsHall, bool $withScreens = false): SportsHall
    {
        $supportsMapsUrl = $this->supportsMapsUrl();
        $supportsCoachAssignments = $this->supportsCoachAssignments();

        $sportsHall = $sportsHall->fresh();

        $relations = [];
        if ($withScreens) {
            $relations[] = 'screens';
        }

        if ($supportsCoachAssignments) {
            $relations[] = 'coaches:id,name,first_name,sports_hall_id,is_active';
        }

        if ($relations !== []) {
            $sportsHall->load($relations);
        }

        if (! $supportsMapsUrl) {
            $sportsHall->maps_url = null;
        }

        if (! $supportsCoachAssignments) {
            $sportsHall->setAttribute('coaches_count', 0);
            $sportsHall->setRelation('coaches', collect());
        }

        return $sportsHall;
    }

    private function supportsMapsUrl(): bool
    {
        return Schema::hasTable('sports_halls') && Schema::hasColumn('sports_halls', 'maps_url');
    }

    private function supportsCoachAssignments(): bool
    {
        return Schema::hasTable('coaches') && Schema::hasColumn('coaches', 'sports_hall_id');
    }

    private function generateUniqueMatricule(string $name, ?int $ignoreId = null): string
    {
        $base = Str::upper(Str::substr(preg_replace('/[^A-Za-z0-9]/', '', Str::ascii($name)), 0, 10));
        $base = $base !== '' ? $base : 'SALLE';

        $counter = 1;
        $candidate = 'SH-'.$base;

        while (
            SportsHall::query()
                ->when($ignoreId !== null, fn ($query) => $query->whereKeyNot($ignoreId))
                ->where('matricule', $candidate)
                ->exists()
        ) {
            $counter++;
            $candidate = 'SH-'.$base.'-'.$counter;
        }

        return $candidate;
    }
}

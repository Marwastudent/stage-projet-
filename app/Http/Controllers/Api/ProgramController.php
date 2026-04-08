<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Program;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    public function index(): JsonResponse
    {
        $programs = Program::orderBy('day')->orderBy('start_time')->paginate(20);

        return response()->json($programs);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'day' => ['required', 'string', 'max:30'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'coach' => ['required', 'string', 'max:120'],
            'room' => ['required', 'string', 'max:120'],
        ]);

        $program = Program::create($data);

        return response()->json([
            'message' => 'Program created successfully.',
            'data' => $program,
        ], 201);
    }

    public function show(Program $program): JsonResponse
    {
        return response()->json([
            'data' => $program,
        ]);
    }

    public function update(Request $request, Program $program): JsonResponse
    {
        $data = $request->validate([
            'day' => ['sometimes', 'required', 'string', 'max:30'],
            'start_time' => ['sometimes', 'required', 'date_format:H:i'],
            'end_time' => ['sometimes', 'required', 'date_format:H:i', 'after:start_time'],
            'coach' => ['sometimes', 'required', 'string', 'max:120'],
            'room' => ['sometimes', 'required', 'string', 'max:120'],
        ]);

        $program->update($data);

        return response()->json([
            'message' => 'Program updated successfully.',
            'data' => $program->fresh(),
        ]);
    }

    public function destroy(Program $program): JsonResponse
    {
        $program->delete();

        return response()->json([
            'message' => 'Program deleted successfully.',
        ]);
    }
}

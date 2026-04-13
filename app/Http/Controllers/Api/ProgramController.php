<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProgramRequest;
use App\Models\Program;
use App\Models\Screen;
use App\Services\ProgramPdfExporter;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProgramController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['day', 'coach', 'room', 'screen_id', 'is_active']);

        $programs = Program::query()
            ->with('screen:id,name,device_key')
            ->filter($filters)
            ->ordered()
            ->paginate(20)
            ->withQueryString();

        return response()->json($programs);
    }

    public function store(ProgramRequest $request): JsonResponse
    {
        $program = Program::create($this->buildPayload($request->validated()));
        $program->load('screen:id,name,device_key');

        return response()->json([
            'message' => 'Programme created successfully.',
            'data' => $program,
        ], 201);
    }

    public function show(Program $program): JsonResponse
    {
        return response()->json([
            'data' => $program->load('screen:id,name,device_key'),
        ]);
    }

    public function exportPdf(Request $request, ProgramPdfExporter $exporter): Response
    {
        $filters = $request->only(['day', 'coach', 'room', 'screen_id', 'is_active']);

        $programs = Program::query()
            ->with('screen:id,name,device_key')
            ->filter($filters)
            ->ordered()
            ->get();

        $filename = 'programs-planning'
            .($request->filled('day') ? '-'.$request->string('day')->toString() : '')
            .'-'.now()->format('Y-m-d')
            .'.pdf';

        return response($exporter->render($programs, $filters), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function update(ProgramRequest $request, Program $program): JsonResponse
    {
        $program->update($this->buildPayload($request->validated(), $program));

        return response()->json([
            'message' => 'Programme updated successfully.',
            'data' => $program->fresh()->load('screen:id,name,device_key'),
        ]);
    }

    public function destroy(Program $program): JsonResponse
    {
        $program->delete();

        return response()->json([
            'message' => 'Programme deleted successfully.',
        ]);
    }

    public function screenFeed(Request $request, Screen $screen): JsonResponse
    {
        $programs = Program::query()
            ->where('screen_id', $screen->id)
            ->when($request->filled('day'), function ($query) use ($request): void {
                $query->where('day', $request->string('day'));
            })
            ->when(
                $request->boolean('active_only', true),
                fn ($query) => $query->where('is_active', true)
            )
            ->ordered()
            ->get();

        return response()->json([
            'screen_id' => $screen->id,
            'screen' => [
                'id' => $screen->id,
                'name' => $screen->name,
                'device_key' => $screen->device_key,
            ],
            'total' => $programs->count(),
            'data' => $programs,
        ]);
    }

    private function buildPayload(array $data, ?Program $program = null): array
    {
        $startTime = (string) ($data['start_time'] ?? $program?->start_time ?? '');
        $duration = (int) ($data['duration'] ?? $program?->duration ?? 0);

        if ($startTime !== '' && $duration > 0) {
            $start = Carbon::createFromFormat('H:i', substr($startTime, 0, 5));

            $data['start_time'] = $start->format('H:i:s');
            $data['end_time'] = $start
                ->addMinutes($duration)
                ->format('H:i:s');
        }

        return $data;
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function index(): JsonResponse
    {
        $media = Media::orderByDesc('created_at')->paginate(20);

        $media->through(fn (Media $item): array => $this->transformMedia($item));

        return response()->json($media);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'file' => ['required', 'file', 'max:102400'],
            'type' => ['nullable', 'in:image,video'],
            'duration' => ['nullable', 'integer', 'min:0'],
        ]);

        $file = $request->file('file');
        $mimeType = (string) $file->getMimeType();

        if (! str_starts_with($mimeType, 'image/') && ! str_starts_with($mimeType, 'video/')) {
            return response()->json([
                'message' => 'Unsupported media type.',
            ], 422);
        }

        $detectedType = str_starts_with($mimeType, 'image/') ? 'image' : 'video';
        $type = $data['type'] ?? $detectedType;
        $path = $file->store('media', 'public');

        $media = Media::create([
            'title' => $data['title'],
            'file_path' => $path,
            'type' => $type,
            'duration' => $data['duration'] ?? ($type === 'image' ? 10 : 0),
        ]);

        return response()->json([
            'message' => 'Media uploaded successfully.',
            'data' => $this->transformMedia($media),
        ], 201);
    }

    public function show(Media $media): JsonResponse
    {
        return response()->json([
            'data' => $this->transformMedia($media),
        ]);
    }

    public function update(Request $request, Media $media): JsonResponse
    {
        $data = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'file' => ['sometimes', 'required', 'file', 'max:102400'],
            'type' => ['sometimes', 'required', 'in:image,video'],
            'duration' => ['sometimes', 'required', 'integer', 'min:0'],
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $mimeType = (string) $file->getMimeType();

            if (! str_starts_with($mimeType, 'image/') && ! str_starts_with($mimeType, 'video/')) {
                return response()->json([
                    'message' => 'Unsupported media type.',
                ], 422);
            }

            if ($media->file_path && Storage::disk('public')->exists($media->file_path)) {
                Storage::disk('public')->delete($media->file_path);
            }

            $media->file_path = $file->store('media', 'public');
            $media->type = $data['type'] ?? (str_starts_with($mimeType, 'image/') ? 'image' : 'video');
        } elseif (isset($data['type'])) {
            $media->type = $data['type'];
        }

        if (isset($data['title'])) {
            $media->title = $data['title'];
        }

        if (isset($data['duration'])) {
            $media->duration = (int) $data['duration'];
        }

        $media->save();

        return response()->json([
            'message' => 'Media updated successfully.',
            'data' => $this->transformMedia($media->fresh()),
        ]);
    }

    public function destroy(Media $media): JsonResponse
    {
        if ($media->file_path && Storage::disk('public')->exists($media->file_path)) {
            Storage::disk('public')->delete($media->file_path);
        }

        $media->delete();

        return response()->json([
            'message' => 'Media deleted successfully.',
        ]);
    }

    private function transformMedia(Media $media): array
    {
        return [
            'id' => $media->id,
            'title' => $media->title,
            'file_path' => $media->file_path,
            'file_url' => url('/storage/'.$media->file_path),
            'type' => $media->type,
            'duration' => $media->duration,
            'created_at' => $media->created_at,
            'updated_at' => $media->updated_at,
        ];
    }
}

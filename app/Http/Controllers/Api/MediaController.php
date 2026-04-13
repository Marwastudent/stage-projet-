<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class MediaController extends Controller
{
    private const MAX_IMAGE_SIZE_KB = 1024;
    private const MAX_VIDEO_DURATION_SECONDS = 300;
    private const MAX_UPLOAD_SIZE_KB = 102400;
    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'];
    private const VIDEO_EXTENSIONS = ['mp4', 'mov', 'avi', 'webm', 'mkv', 'm4v', 'mpeg', 'mpg'];

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
            'file' => ['required', 'file', 'max:'.self::MAX_UPLOAD_SIZE_KB],
            'type' => ['nullable', 'in:image,video'],
            'duration' => ['nullable', 'integer', 'min:0'],
        ]);

        $file = $request->file('file');
        $type = $this->validateUploadedMedia($file, $data);
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
            'file' => ['sometimes', 'required', 'file', 'max:'.self::MAX_UPLOAD_SIZE_KB],
            'type' => ['sometimes', 'required', 'in:image,video'],
            'duration' => ['sometimes', 'required', 'integer', 'min:0'],
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $mediaType = $this->validateUploadedMedia($file, $data);

            if ($media->file_path && Storage::disk('public')->exists($media->file_path)) {
                Storage::disk('public')->delete($media->file_path);
            }

            $media->file_path = $file->store('media', 'public');
            $media->type = $mediaType;
        } elseif (isset($data['type'])) {
            $media->type = $data['type'];
        }

        if (isset($data['title'])) {
            $media->title = $data['title'];
        }

        if (isset($data['duration'])) {
            $media->duration = (int) $data['duration'];
        }

        if ($media->type === 'video' && $media->duration > self::MAX_VIDEO_DURATION_SECONDS) {
            throw ValidationException::withMessages([
                'duration' => ['La duree de la video ne doit pas depasser 5 minutes.'],
            ]);
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

    private function validateUploadedMedia(UploadedFile $file, array $data): string
    {
        $detectedType = $this->detectMediaType($file, $data);
        $requestedType = $data['type'] ?? null;

        if ($requestedType !== null && $requestedType !== $detectedType) {
            throw ValidationException::withMessages([
                'type' => ['Le type selectionne ne correspond pas au fichier envoye.'],
            ]);
        }

        if ($detectedType === 'image' && $file->getSize() > (self::MAX_IMAGE_SIZE_KB * 1024)) {
            throw ValidationException::withMessages([
                'file' => ["L'image ne doit pas depasser 1 Mo."],
            ]);
        }

        if ($detectedType === 'video') {
            $duration = $data['duration'] ?? null;

            if ($duration === null) {
                throw ValidationException::withMessages([
                    'duration' => ['La duree de la video est requise pour verifier la limite de 5 minutes.'],
                ]);
            }

            if ((int) $duration > self::MAX_VIDEO_DURATION_SECONDS) {
                throw ValidationException::withMessages([
                    'duration' => ['La duree de la video ne doit pas depasser 5 minutes.'],
                ]);
            }
        }

        return $detectedType;
    }

    private function detectMediaType(UploadedFile $file, array $data): string
    {
        $mimeType = strtolower((string) $file->getMimeType());

        if (str_starts_with($mimeType, 'image/')) {
            return 'image';
        }

        if (str_starts_with($mimeType, 'video/')) {
            return 'video';
        }

        $extension = strtolower((string) $file->getClientOriginalExtension());

        if (in_array($extension, self::IMAGE_EXTENSIONS, true)) {
            return 'image';
        }

        if (in_array($extension, self::VIDEO_EXTENSIONS, true)) {
            return 'video';
        }

        $requestedType = $data['type'] ?? null;

        if (in_array($requestedType, ['image', 'video'], true)) {
            return $requestedType;
        }

        throw ValidationException::withMessages([
            'file' => ['Type de media non supporte.'],
        ]);
    }
}

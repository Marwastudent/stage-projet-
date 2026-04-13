<?php

namespace Tests\Feature;

use App\Models\ApiToken;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_upload_an_image_up_to_one_megabyte(): void
    {
        Storage::fake('public');
        $headers = $this->authHeaders();

        $response = $this->post('/api/media', [
            'title' => 'Affiche club',
            'type' => 'image',
            'duration' => 10,
            'file' => UploadedFile::fake()->image('affiche.jpg')->size(1024),
        ], $headers);

        $response
            ->assertCreated()
            ->assertJsonPath('data.type', 'image');
    }

    public function test_admin_cannot_upload_an_image_larger_than_one_megabyte(): void
    {
        Storage::fake('public');
        $headers = $this->authHeaders();

        $response = $this->post('/api/media', [
            'title' => 'Image lourde',
            'type' => 'image',
            'duration' => 10,
            'file' => UploadedFile::fake()->image('heavy.jpg')->size(1025),
        ], $headers);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['file']);
    }

    public function test_admin_cannot_upload_a_video_longer_than_five_minutes(): void
    {
        Storage::fake('public');
        $headers = $this->authHeaders();

        $response = $this->post('/api/media', [
            'title' => 'Video longue',
            'type' => 'video',
            'duration' => 301,
            'file' => UploadedFile::fake()->create('clip.mp4', 512, 'video/mp4'),
        ], $headers);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['duration']);
    }

    public function test_admin_can_upload_a_video_with_duration_up_to_five_minutes(): void
    {
        Storage::fake('public');
        $headers = $this->authHeaders();

        $response = $this->post('/api/media', [
            'title' => 'Video courte',
            'type' => 'video',
            'duration' => 300,
            'file' => UploadedFile::fake()->create('clip.mp4', 512, 'video/mp4'),
        ], $headers);

        $response
            ->assertCreated()
            ->assertJsonPath('data.type', 'video')
            ->assertJsonPath('data.duration', 300);
    }

    private function authHeaders(string $role = 'admin'): array
    {
        $user = User::factory()->create([
            'role' => $role,
        ]);

        $plainToken = 'token-'.$role.'-'.$user->id;

        ApiToken::create([
            'user_id' => $user->id,
            'name' => 'phpunit',
            'token_hash' => hash('sha256', $plainToken),
            'expires_at' => now()->addDay(),
        ]);

        return [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.$plainToken,
        ];
    }
}

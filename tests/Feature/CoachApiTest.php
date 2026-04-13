<?php

namespace Tests\Feature;

use App\Models\ApiToken;
use App\Models\Coach;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoachApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_list_coaches(): void
    {
        $headers = $this->authHeaders('manager');

        Coach::create([
            'name' => 'Salma Idrissi',
            'first_name' => 'Salma',
            'email' => 'salma@example.com',
            'specialty' => 'Yoga',
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/coaches', $headers);

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Salma Idrissi')
            ->assertJsonPath('data.0.first_name', 'Salma')
            ->assertJsonPath('data.0.specialty', 'Yoga');
    }

    public function test_admin_can_create_a_coach(): void
    {
        $headers = $this->authHeaders();

        $response = $this->postJson('/api/coaches', [
            'name' => 'Karim Atlas',
            'first_name' => 'Karim',
            'email' => 'karim@example.com',
            'specialty' => 'Cross Training',
            'is_active' => true,
        ], $headers);

        $response
            ->assertCreated()
            ->assertJsonPath('data.name', 'Karim Atlas')
            ->assertJsonPath('data.first_name', 'Karim')
            ->assertJsonPath('data.email', 'karim@example.com')
            ->assertJsonPath('data.specialty', 'Cross Training')
            ->assertJsonPath('data.is_active', true);

        $this->assertDatabaseHas('coaches', [
            'name' => 'Karim Atlas',
            'first_name' => 'Karim',
            'email' => 'karim@example.com',
            'specialty' => 'Cross Training',
            'is_active' => 1,
        ]);
    }

    public function test_admin_can_update_a_coach(): void
    {
        $headers = $this->authHeaders();

        $coach = Coach::create([
            'name' => 'Nadia Coach',
            'first_name' => 'Nadia',
            'email' => 'nadia@example.com',
            'specialty' => 'Pilates',
            'is_active' => true,
        ]);

        $response = $this->putJson("/api/coaches/{$coach->id}", [
            'first_name' => 'Nadia Sofia',
            'specialty' => 'Pilates avance',
            'is_active' => false,
        ], $headers);

        $response
            ->assertOk()
            ->assertJsonPath('data.first_name', 'Nadia Sofia')
            ->assertJsonPath('data.specialty', 'Pilates avance')
            ->assertJsonPath('data.is_active', false);

        $this->assertDatabaseHas('coaches', [
            'id' => $coach->id,
            'first_name' => 'Nadia Sofia',
            'specialty' => 'Pilates avance',
            'is_active' => 0,
        ]);
    }

    public function test_admin_can_delete_a_coach(): void
    {
        $headers = $this->authHeaders();

        $coach = Coach::create([
            'name' => 'Coach Delete',
            'is_active' => true,
        ]);

        $response = $this->deleteJson("/api/coaches/{$coach->id}", [], $headers);

        $response->assertOk();
        $this->assertDatabaseMissing('coaches', ['id' => $coach->id]);
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

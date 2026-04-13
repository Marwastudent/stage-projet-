<?php

namespace Tests\Feature;

use App\Models\ApiToken;
use App\Models\Screen;
use App\Models\SportsHall;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScreenApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_screen_without_emplacement(): void
    {
        $headers = $this->authHeaders();
        $hall = $this->createSportsHall();

        $response = $this->postJson('/api/screens', [
            'name' => 'Ecran accueil',
            'sports_hall_id' => $hall->id,
            'status' => 'online',
        ], $headers);

        $response
            ->assertCreated()
            ->assertJsonPath('data.name', 'Ecran accueil')
            ->assertJsonPath('data.status', 'online')
            ->assertJsonPath('data.sports_hall_id', $hall->id);

        $this->assertDatabaseHas('screens', [
            'name' => 'Ecran accueil',
            'sports_hall_id' => $hall->id,
            'status' => 'online',
            'emplacement' => 'entree',
        ]);
    }

    public function test_admin_can_update_screen_status_without_sending_emplacement(): void
    {
        $headers = $this->authHeaders();
        $screen = $this->createScreen();

        $response = $this->putJson("/api/screens/{$screen->id}", [
            'status' => 'online',
        ], $headers);

        $response
            ->assertOk()
            ->assertJsonPath('data.status', 'online');

        $this->assertDatabaseHas('screens', [
            'id' => $screen->id,
            'status' => 'online',
            'emplacement' => 'entree',
        ]);
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

    private function createSportsHall(): SportsHall
    {
        return SportsHall::create([
            'name' => 'Club Test',
            'matricule' => 'CLUB-TEST',
            'localisation' => 'Casablanca',
        ]);
    }

    private function createScreen(): Screen
    {
        $hall = $this->createSportsHall();

        return Screen::create([
            'name' => 'Ecran test',
            'emplacement' => 'entree',
            'sports_hall_id' => $hall->id,
            'device_key' => 'SCR-TEST-0001',
            'status' => 'offline',
        ]);
    }
}

<?php

namespace Tests\Feature;

use App\Models\ApiToken;
use App\Models\Coach;
use App\Models\SportsHall;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SportsHallApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_sports_hall_with_automatic_matricule_maps_and_coaches(): void
    {
        $headers = $this->authHeaders();
        $coachA = $this->createCoach('Salma', 'Idrissi');
        $coachB = $this->createCoach('Karim', 'Atlas');

        $response = $this->postJson('/api/sports-halls', [
            'name' => 'Atlas Fitness Casa',
            'localisation' => 'Casablanca',
            'maps_url' => 'https://maps.google.com/?q=Casablanca',
            'coach_ids' => [$coachA->id, $coachB->id],
        ], $headers);

        $response
            ->assertCreated()
            ->assertJsonPath('data.name', 'Atlas Fitness Casa')
            ->assertJsonPath('data.localisation', 'Casablanca')
            ->assertJsonPath('data.maps_url', 'https://maps.google.com/?q=Casablanca')
            ->assertJsonPath('data.matricule', 'SH-ATLASFITNE');

        $sportsHallId = (int) $response->json('data.id');

        $this->assertDatabaseHas('sports_halls', [
            'id' => $sportsHallId,
            'matricule' => 'SH-ATLASFITNE',
            'maps_url' => 'https://maps.google.com/?q=Casablanca',
        ]);

        $this->assertDatabaseHas('coaches', [
            'id' => $coachA->id,
            'sports_hall_id' => $sportsHallId,
        ]);

        $this->assertDatabaseHas('coaches', [
            'id' => $coachB->id,
            'sports_hall_id' => $sportsHallId,
        ]);
    }

    public function test_admin_can_update_a_sports_hall_and_reassign_coaches(): void
    {
        $headers = $this->authHeaders();
        $sportsHall = SportsHall::create([
            'name' => 'Club Initial',
            'matricule' => 'SH-CLUBINITIA',
            'localisation' => 'Rabat',
        ]);

        $coachA = $this->createCoach('Nadia', 'Coach', $sportsHall->id);
        $coachB = $this->createCoach('Sofia', 'Move');

        $response = $this->putJson("/api/sports-halls/{$sportsHall->id}", [
            'name' => 'Club Premium Rabat',
            'localisation' => 'Rabat Agdal',
            'maps_url' => 'https://maps.google.com/?q=Rabat+Agdal',
            'coach_ids' => [$coachB->id],
        ], $headers);

        $response
            ->assertOk()
            ->assertJsonPath('data.name', 'Club Premium Rabat')
            ->assertJsonPath('data.localisation', 'Rabat Agdal')
            ->assertJsonPath('data.maps_url', 'https://maps.google.com/?q=Rabat+Agdal')
            ->assertJsonPath('data.matricule', 'SH-CLUBPREMIU');

        $this->assertDatabaseHas('coaches', [
            'id' => $coachA->id,
            'sports_hall_id' => null,
        ]);

        $this->assertDatabaseHas('coaches', [
            'id' => $coachB->id,
            'sports_hall_id' => $sportsHall->id,
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

    private function createCoach(string $firstName, string $name, ?int $sportsHallId = null): Coach
    {
        return Coach::create([
            'name' => $name,
            'first_name' => $firstName,
            'email' => strtolower($firstName.'.'.$name).'@example.com',
            'specialty' => 'Fitness',
            'sports_hall_id' => $sportsHallId,
            'is_active' => true,
        ]);
    }
}

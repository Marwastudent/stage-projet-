<?php

namespace Tests\Feature;

use App\Models\Program;
use App\Models\Screen;
use App\Models\SportsHall;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlayerPlanningFeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_player_feed_returns_planning_programs_for_screen_mode(): void
    {
        $screen = $this->createScreen();

        Program::create([
            'title' => 'Yoga du matin',
            'course_type' => 'yoga',
            'day' => 'lundi',
            'start_time' => '08:00:00',
            'end_time' => '09:00:00',
            'duration' => 60,
            'coach' => 'Salma',
            'room' => 'Studio A',
            'screen_id' => $screen->id,
            'display_order' => 1,
            'is_active' => true,
        ]);

        Program::create([
            'title' => 'Cours inactif',
            'course_type' => 'pilates',
            'day' => 'mardi',
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'duration' => 60,
            'coach' => 'Nadia',
            'room' => 'Studio B',
            'screen_id' => $screen->id,
            'display_order' => 1,
            'is_active' => false,
        ]);

        $response = $this->getJson("/api/player/{$screen->device_key}?mode=planning");

        $response
            ->assertOk()
            ->assertJsonPath('data.mode', 'planning')
            ->assertJsonPath('data.screen.device_key', $screen->device_key)
            ->assertJsonPath('data.server_clock.timezone', config('app.timezone'))
            ->assertJsonStructure([
                'data' => [
                    'server_clock' => ['iso', 'timezone', 'date', 'time', 'day_key'],
                ],
            ])
            ->assertJsonCount(1, 'data.programs')
            ->assertJsonPath('data.programs.0.title', 'Yoga du matin')
            ->assertJsonPath('data.programs.0.day', 'lundi')
            ->assertJsonPath('data.programs.0.start_time', '08:00')
            ->assertJsonPath('data.programs.0.computed_end_time', '09:00');
    }

    public function test_player_feed_falls_back_to_planning_when_playlist_is_empty(): void
    {
        $screen = $this->createScreen();

        Program::create([
            'title' => 'Cycling Evening',
            'course_type' => 'cycling',
            'day' => 'vendredi',
            'start_time' => '18:30:00',
            'end_time' => '19:30:00',
            'duration' => 60,
            'coach' => 'Karim',
            'room' => 'Bike Room',
            'screen_id' => $screen->id,
            'display_order' => 1,
            'is_active' => true,
        ]);

        $response = $this->getJson("/api/player/{$screen->device_key}");

        $response
            ->assertOk()
            ->assertJsonPath('data.mode', 'planning')
            ->assertJsonPath('data.screen.device_key', $screen->device_key)
            ->assertJsonPath('data.server_clock.timezone', config('app.timezone'))
            ->assertJsonCount(1, 'data.programs')
            ->assertJsonPath('data.programs.0.title', 'Cycling Evening')
            ->assertJsonPath('data.programs.0.day', 'vendredi');
    }

    public function test_player_feed_exposes_screen_status_for_offline_planning_screen(): void
    {
        $screen = $this->createScreen('offline');

        $response = $this->getJson("/api/player/{$screen->device_key}?mode=planning");

        $response
            ->assertOk()
            ->assertJsonPath('data.screen.device_key', $screen->device_key)
            ->assertJsonPath('data.screen.status', 'offline');
    }

    private function createScreen(string $status = 'online'): Screen
    {
        $hall = SportsHall::create([
            'name' => 'Club Planning',
            'matricule' => 'PLAN-001',
            'localisation' => 'Casablanca',
        ]);

        return Screen::create([
            'name' => 'Ecran Planning',
            'emplacement' => 'entree',
            'sports_hall_id' => $hall->id,
            'device_key' => 'SCR-PLAN-0001',
            'status' => $status,
        ]);
    }
}

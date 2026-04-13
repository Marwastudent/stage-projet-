<?php

namespace Tests\Feature;

use App\Models\ApiToken;
use App\Models\Program;
use App\Models\Screen;
use App\Models\SportsHall;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use setasign\Fpdi\Fpdi;
use Tests\TestCase;

class ProgramApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_an_enriched_program(): void
    {
        $headers = $this->authHeaders();
        $screen = $this->createScreen();

        $response = $this->postJson('/api/programs', [
            'title' => 'Yoga matinal',
            'course_type' => 'yoga',
            'day' => 'lundi',
            'start_time' => '09:00',
            'duration' => 60,
            'coach' => 'Sara',
            'room' => 'Salle A',
            'screen_id' => $screen->id,
            'display_order' => 2,
            'is_active' => true,
        ], $headers);

        $response
            ->assertCreated()
            ->assertJsonPath('data.title', 'Yoga matinal')
            ->assertJsonPath('data.screen.id', $screen->id)
            ->assertJsonPath('data.computed_end_time', '10:00');

        $this->assertDatabaseHas('programs', [
            'title' => 'Yoga matinal',
            'course_type' => 'yoga',
            'day' => 'lundi',
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
            'duration' => 60,
            'coach' => 'Sara',
            'room' => 'Salle A',
            'screen_id' => $screen->id,
            'display_order' => 2,
            'is_active' => 1,
        ]);
    }

    public function test_program_conflict_on_same_room_and_day_is_rejected(): void
    {
        $headers = $this->authHeaders();
        $screen = $this->createScreen();

        Program::create([
            'title' => 'Pilates 9h',
            'course_type' => 'pilates',
            'day' => 'mardi',
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
            'duration' => 60,
            'coach' => 'Nora',
            'room' => 'Salle B',
            'screen_id' => $screen->id,
            'display_order' => 1,
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/programs', [
            'title' => 'Cardio 9h30',
            'course_type' => 'cardio',
            'day' => 'mardi',
            'start_time' => '09:30',
            'duration' => 45,
            'coach' => 'Yanis',
            'room' => 'Salle B',
            'screen_id' => $screen->id,
            'display_order' => 2,
            'is_active' => true,
        ], $headers);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['room']);
    }

    public function test_authenticated_user_can_fetch_programs_for_a_screen(): void
    {
        $headers = $this->authHeaders('manager');
        $screen = $this->createScreen('Ecran A');
        $otherScreen = $this->createScreen('Ecran B');

        Program::create([
            'title' => 'Zumba midi',
            'course_type' => 'zumba',
            'day' => 'mercredi',
            'start_time' => '12:00:00',
            'end_time' => '12:45:00',
            'duration' => 45,
            'coach' => 'Lina',
            'room' => 'Studio 1',
            'screen_id' => $screen->id,
            'display_order' => 1,
            'is_active' => true,
        ]);

        Program::create([
            'title' => 'Cours archive',
            'course_type' => 'yoga',
            'day' => 'mercredi',
            'start_time' => '13:00:00',
            'end_time' => '14:00:00',
            'duration' => 60,
            'coach' => 'Maya',
            'room' => 'Studio 2',
            'screen_id' => $screen->id,
            'display_order' => 2,
            'is_active' => false,
        ]);

        Program::create([
            'title' => 'Autre ecran',
            'course_type' => 'boxing',
            'day' => 'mercredi',
            'start_time' => '12:00:00',
            'end_time' => '13:00:00',
            'duration' => 60,
            'coach' => 'Riad',
            'room' => 'Studio 3',
            'screen_id' => $otherScreen->id,
            'display_order' => 1,
            'is_active' => true,
        ]);

        $response = $this->getJson("/api/screens/{$screen->id}/programs?active_only=1", $headers);

        $response
            ->assertOk()
            ->assertJsonPath('screen_id', $screen->id)
            ->assertJsonPath('total', 1)
            ->assertJsonPath('data.0.title', 'Zumba midi')
            ->assertJsonPath('data.0.computed_end_time', '12:45');
    }

    public function test_authenticated_user_can_download_programs_pdf(): void
    {
        $headers = $this->authHeaders('manager');
        $screen = $this->createScreen('Ecran planning');

        $templatePath = storage_path('framework/testing/program-template-test.pdf');
        $templateDirectory = dirname($templatePath);

        if (! is_dir($templateDirectory)) {
            mkdir($templateDirectory, 0777, true);
        }

        $template = new \FPDF('P', 'mm', 'A4');
        $template->AddPage();
        $template->SetFillColor(15, 15, 15);
        $template->Rect(0, 0, 210, 297, 'F');
        $template->SetFillColor(170, 20, 20);
        $template->Rect(15, 18, 180, 22, 'F');
        $template->Rect(15, 74, 180, 14, 'F');
        $template->Output('F', $templatePath);

        config([
            'programs.pdf_template' => $templatePath,
            'programs.pdf_rows_per_page' => 10,
        ]);

        Program::create([
            'title' => 'Boxe',
            'course_type' => 'cardio',
            'day' => 'lundi',
            'start_time' => '08:30:00',
            'end_time' => '09:15:00',
            'duration' => 45,
            'coach' => 'Rachid',
            'room' => 'Salle Rouge',
            'screen_id' => $screen->id,
            'display_order' => 1,
            'is_active' => true,
        ]);

        Program::create([
            'title' => 'Yoga',
            'course_type' => 'mobilite',
            'day' => 'mardi',
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'duration' => 60,
            'coach' => 'Sofia',
            'room' => 'Studio Zen',
            'screen_id' => $screen->id,
            'display_order' => 1,
            'is_active' => true,
        ]);

        $response = $this->get('/api/programs/export/pdf', $headers);

        $response
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');

        $content = $response->getContent();

        $this->assertIsString($content);
        $this->assertStringStartsWith('%PDF', $content);
        $this->assertStringContainsString('attachment; filename="programs-planning-', (string) $response->headers->get('Content-Disposition'));

        $outputPath = storage_path('framework/testing/program-export-output.pdf');
        file_put_contents($outputPath, $content);

        $inspector = new Fpdi();
        $pageCount = $inspector->setSourceFile($outputPath);

        $this->assertSame(2, $pageCount);
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

    private function createScreen(string $name = 'Ecran principal'): Screen
    {
        $hall = SportsHall::create([
            'name' => 'Club Atlas '.$name,
            'matricule' => 'MAT-'.strtoupper(str_replace(' ', '', $name)),
            'localisation' => 'Casablanca',
        ]);

        return Screen::create([
            'name' => $name,
            'emplacement' => 'entree',
            'sports_hall_id' => $hall->id,
            'device_key' => 'SCR-'.strtoupper(substr(md5($name), 0, 10)),
            'status' => 'online',
        ]);
    }
}

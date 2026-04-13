<?php

namespace Database\Seeders;

use App\Models\AdSchedule;
use App\Models\Coach;
use App\Models\Media;
use App\Models\Playlist;
use App\Models\PlaylistItem;
use App\Models\Program;
use App\Models\Screen;
use App\Models\ScreenPlaylist;
use App\Models\SportsHall;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class DemoClubSeeder extends Seeder
{
    public function run(): void
    {
        $halls = $this->seedSportsHalls();
        $coaches = $this->seedCoaches($halls);
        $screens = $this->seedScreens($halls);
        $media = $this->seedMediaLibrary();
        $playlists = $this->seedPlaylists();

        $this->seedUsers();
        $this->seedPlaylistItems($playlists, $media);
        $this->seedScreenAssignments($screens, $playlists);
        $this->seedAdSchedules($screens, $media);
        $this->seedPrograms($screens, $coaches);
    }

    private function seedUsers(): void
    {
        $users = [
            [
                'email' => 'marwaaitbahadou4@gmail.com',
                'name' => 'Admin',
                'password' => 'password123',
                'role' => 'admin',
            ],
            [
                'email' => 'aitbahadoumarwa16@gmail.com',
                'name' => 'Manager',
                'password' => 'Manager@2026',
                'role' => 'manager',
            ],
            [
                'email' => 'client@club.local',
                'name' => 'Client',
                'password' => 'password123',
                'role' => 'client',
            ],
            [
                'email' => 'reception.casa@club.local',
                'name' => 'Reception Casa',
                'password' => 'DemoClub2026',
                'role' => 'manager',
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'password' => $user['password'],
                    'role' => $user['role'],
                ]
            );
        }
    }

    /**
     * @return array<string, \App\Models\SportsHall>
     */
    private function seedSportsHalls(): array
    {
        $supportsMapsUrl = Schema::hasColumn('sports_halls', 'maps_url');

        $records = [
            'atlas-casa' => [
                'matricule' => 'HALL-CASA-001',
                'name' => 'Atlas Fitness Casa',
                'localisation' => 'Casablanca - Maarif',
                'maps_url' => null,
            ],
            'rabat-center' => [
                'matricule' => 'HALL-RABAT-002',
                'name' => 'Rabat Sport Center',
                'localisation' => 'Rabat - Agdal',
                'maps_url' => null,
            ],
            'marrakech-hub' => [
                'matricule' => 'HALL-MARRAKECH-003',
                'name' => 'Marrakech Wellness Hub',
                'localisation' => 'Marrakech - Gueliz',
                'maps_url' => null,
            ],
        ];

        $halls = [];

        foreach ($records as $key => $record) {
            $payload = [
                'name' => $record['name'],
                'localisation' => $record['localisation'],
            ];

            if ($supportsMapsUrl) {
                $payload['maps_url'] = $record['maps_url'];
            }

            $halls[$key] = SportsHall::updateOrCreate(
                ['matricule' => $record['matricule']],
                $payload
            );
        }

        return $halls;
    }

    /**
     * @param  array<string, \App\Models\SportsHall>  $halls
     * @return array<string, \App\Models\Coach>
     */
    private function seedCoaches(array $halls): array
    {
        $supportsSportsHallId = Schema::hasColumn('coaches', 'sports_hall_id');

        $records = [
            'karim-bennani' => [
                'first_name' => 'Karim',
                'name' => 'Bennani',
                'email' => 'karim.bennani@club.local',
                'specialty' => 'Cross Training',
                'hall' => 'atlas-casa',
            ],
            'sarah-idrissi' => [
                'first_name' => 'Sarah',
                'name' => 'El Idrissi',
                'email' => 'sarah.idrissi@club.local',
                'specialty' => 'Cycling',
                'hall' => 'atlas-casa',
            ],
            'yassine-amrani' => [
                'first_name' => 'Yassine',
                'name' => 'Amrani',
                'email' => 'yassine.amrani@club.local',
                'specialty' => 'Strength',
                'hall' => 'atlas-casa',
            ],
            'salma-tazi' => [
                'first_name' => 'Salma',
                'name' => 'Tazi',
                'email' => 'salma.tazi@club.local',
                'specialty' => 'Yoga Flow',
                'hall' => 'rabat-center',
            ],
            'mehdi-alaoui' => [
                'first_name' => 'Mehdi',
                'name' => 'Alaoui',
                'email' => 'mehdi.alaoui@club.local',
                'specialty' => 'HIIT',
                'hall' => 'rabat-center',
            ],
            'leila-cherkaoui' => [
                'first_name' => 'Leila',
                'name' => 'Cherkaoui',
                'email' => 'leila.cherkaoui@club.local',
                'specialty' => 'Pilates',
                'hall' => 'rabat-center',
            ],
            'nabil-berrada' => [
                'first_name' => 'Nabil',
                'name' => 'Berrada',
                'email' => 'nabil.berrada@club.local',
                'specialty' => 'Functional Training',
                'hall' => 'marrakech-hub',
            ],
            'ines-othmani' => [
                'first_name' => 'Ines',
                'name' => 'Othmani',
                'email' => 'ines.othmani@club.local',
                'specialty' => 'Mobility',
                'hall' => 'marrakech-hub',
            ],
            'omar-ziani' => [
                'first_name' => 'Omar',
                'name' => 'Ziani',
                'email' => 'omar.ziani@club.local',
                'specialty' => 'Boxing',
                'hall' => 'marrakech-hub',
            ],
        ];

        $coaches = [];

        foreach ($records as $key => $record) {
            $payload = [
                'first_name' => $record['first_name'],
                'name' => $record['name'],
                'specialty' => $record['specialty'],
                'is_active' => true,
            ];

            if ($supportsSportsHallId) {
                $payload['sports_hall_id'] = $halls[$record['hall']]->id;
            }

            $coaches[$key] = Coach::updateOrCreate(
                ['email' => $record['email']],
                $payload
            );
        }

        return $coaches;
    }

    /**
     * @param  array<string, \App\Models\SportsHall>  $halls
     * @return array<string, \App\Models\Screen>
     */
    private function seedScreens(array $halls): array
    {
        $supportsLocalisation = Schema::hasColumn('screens', 'localisation');

        $records = [
            'atlas-accueil' => [
                'device_key' => 'SCR-ATLAS-CASA01',
                'name' => 'Accueil Maarif',
                'emplacement' => 'entree',
                'status' => 'online',
                'hall' => 'atlas-casa',
            ],
            'atlas-cycle' => [
                'device_key' => 'SCR-ATLAS-CYCLE',
                'name' => 'Studio Cycling',
                'emplacement' => 'sortie',
                'status' => 'online',
                'hall' => 'atlas-casa',
            ],
            'rabat-lobby' => [
                'device_key' => 'SCR-RABAT-LOBBY',
                'name' => 'Lobby Agdal',
                'emplacement' => 'entree',
                'status' => 'online',
                'hall' => 'rabat-center',
            ],
            'rabat-cross' => [
                'device_key' => 'SCR-RABAT-CROSS',
                'name' => 'Zone Functional',
                'emplacement' => 'cafeteria',
                'status' => 'online',
                'hall' => 'rabat-center',
            ],
            'marrakech-lounge' => [
                'device_key' => 'SCR-MARRAKECH-LOUNGE',
                'name' => 'Reception Gueliz',
                'emplacement' => 'entree',
                'status' => 'online',
                'hall' => 'marrakech-hub',
            ],
        ];

        $screens = [];

        foreach ($records as $key => $record) {
            $hall = $halls[$record['hall']];

            $payload = [
                'name' => $record['name'],
                'emplacement' => $record['emplacement'],
                'sports_hall_id' => $hall->id,
                'status' => $record['status'],
            ];

            if ($supportsLocalisation) {
                $payload['localisation'] = $hall->localisation;
            }

            $screens[$key] = Screen::updateOrCreate(
                ['device_key' => $record['device_key']],
                $payload
            );
        }

        return $screens;
    }

    /**
     * @return array<string, \App\Models\Media>
     */
    private function seedMediaLibrary(): array
    {
        $library = [
            'welcome-atlas' => [
                'title' => 'Bienvenue Atlas Fitness Casa',
                'subtitle' => 'Clubbing premium, coaching et recuperation',
                'kicker' => 'CASABLANCA MAARIF',
                'accent' => '#A57E1C',
                'duration' => 12,
            ],
            'coaching-premium' => [
                'title' => 'Coaching premium',
                'subtitle' => 'Bilans mensuels, suivi nutrition et objectif forme',
                'kicker' => 'SERVICE PERSONNALISE',
                'accent' => '#D35400',
                'duration' => 10,
            ],
            'cycling-zone' => [
                'title' => 'Studio cycling',
                'subtitle' => 'Cours immersifs matin, midi et soir',
                'kicker' => 'ENERGIE COLLECTIVE',
                'accent' => '#0E8A6A',
                'duration' => 10,
            ],
            'functional-zone' => [
                'title' => 'Zone functional',
                'subtitle' => 'Bootcamp, HIIT, boxing et renforcement',
                'kicker' => 'PERFORMANCE',
                'accent' => '#B23A48',
                'duration' => 11,
            ],
            'wellness-zone' => [
                'title' => 'Recovery and mobility',
                'subtitle' => 'Stretching, respiration et retour au calme',
                'kicker' => 'WELLNESS HUB',
                'accent' => '#3C6E71',
                'duration' => 12,
            ],
            'nutrition-bar' => [
                'title' => 'Nutrition bar',
                'subtitle' => 'Smoothies proteines, snacks sains et hydration',
                'kicker' => 'ENERGIE DU JOUR',
                'accent' => '#6D597A',
                'duration' => 9,
            ],
        ];

        $media = [];
        Storage::disk('public')->makeDirectory('media/demo');

        foreach ($library as $key => $item) {
            $path = 'media/demo/'.$key.'.svg';

            Storage::disk('public')->put($path, $this->buildPosterSvg(
                $item['title'],
                $item['subtitle'],
                $item['kicker'],
                $item['accent']
            ));

            $media[$key] = Media::updateOrCreate(
                ['file_path' => $path],
                [
                    'title' => $item['title'],
                    'type' => 'image',
                    'duration' => $item['duration'],
                ]
            );
        }

        return $media;
    }

    /**
     * @return array<string, \App\Models\Playlist>
     */
    private function seedPlaylists(): array
    {
        $names = [
            'atlas-lobby' => 'Atlas Lobby Loop',
            'atlas-performance' => 'Atlas Performance Zone',
            'rabat-lobby' => 'Rabat Lobby Highlights',
            'marrakech-wellness' => 'Marrakech Wellness Flow',
        ];

        $playlists = [];

        foreach ($names as $key => $name) {
            $playlists[$key] = Playlist::updateOrCreate(
                ['name' => $name],
                ['name' => $name]
            );
        }

        return $playlists;
    }

    /**
     * @param  array<string, \App\Models\Playlist>  $playlists
     * @param  array<string, \App\Models\Media>  $media
     */
    private function seedPlaylistItems(array $playlists, array $media): void
    {
        $mapping = [
            'atlas-lobby' => ['welcome-atlas', 'coaching-premium', 'nutrition-bar'],
            'atlas-performance' => ['cycling-zone', 'functional-zone', 'coaching-premium'],
            'rabat-lobby' => ['functional-zone', 'coaching-premium', 'nutrition-bar'],
            'marrakech-wellness' => ['wellness-zone', 'welcome-atlas', 'nutrition-bar'],
        ];

        foreach ($mapping as $playlistKey => $mediaKeys) {
            foreach ($mediaKeys as $index => $mediaKey) {
                PlaylistItem::updateOrCreate(
                    [
                        'playlist_id' => $playlists[$playlistKey]->id,
                        'order' => $index + 1,
                    ],
                    [
                        'media_id' => $media[$mediaKey]->id,
                        'duration_override' => null,
                    ]
                );
            }
        }
    }

    /**
     * @param  array<string, \App\Models\Screen>  $screens
     * @param  array<string, \App\Models\Playlist>  $playlists
     */
    private function seedScreenAssignments(array $screens, array $playlists): void
    {
        $startsAt = now()->subDays(10)->startOfDay();
        $endsAt = now()->addMonths(6)->endOfDay();

        $mapping = [
            'atlas-accueil' => 'atlas-lobby',
            'atlas-cycle' => 'atlas-performance',
            'rabat-lobby' => 'rabat-lobby',
            'rabat-cross' => 'atlas-performance',
            'marrakech-lounge' => 'marrakech-wellness',
        ];

        foreach ($mapping as $screenKey => $playlistKey) {
            $screen = $screens[$screenKey];
            $playlist = $playlists[$playlistKey];

            ScreenPlaylist::where('screen_id', $screen->id)
                ->where('playlist_id', '!=', $playlist->id)
                ->update(['is_active' => false]);

            ScreenPlaylist::updateOrCreate(
                [
                    'screen_id' => $screen->id,
                    'playlist_id' => $playlist->id,
                ],
                [
                    'is_active' => true,
                    'starts_at' => $startsAt,
                    'ends_at' => $endsAt,
                ]
            );
        }
    }

    /**
     * @param  array<string, \App\Models\Screen>  $screens
     * @param  array<string, \App\Models\Media>  $media
     */
    private function seedAdSchedules(array $screens, array $media): void
    {
        $ads = [
            [
                'name' => 'Coaching Premium Casa',
                'screen' => 'atlas-accueil',
                'media' => 'coaching-premium',
                'display_every_loops' => 2,
            ],
            [
                'name' => 'Nutrition Bar Rabat',
                'screen' => 'rabat-lobby',
                'media' => 'nutrition-bar',
                'display_every_loops' => 3,
            ],
            [
                'name' => 'Recovery Marrakech',
                'screen' => 'marrakech-lounge',
                'media' => 'wellness-zone',
                'display_every_loops' => 2,
            ],
        ];

        foreach ($ads as $ad) {
            AdSchedule::updateOrCreate(
                [
                    'name' => $ad['name'],
                    'screen_id' => $screens[$ad['screen']]->id,
                ],
                [
                    'media_id' => $media[$ad['media']]->id,
                    'starts_at' => now()->subDays(3)->startOfDay(),
                    'ends_at' => now()->addMonths(6)->endOfDay(),
                    'display_every_loops' => $ad['display_every_loops'],
                    'duration_override' => null,
                    'is_active' => true,
                ]
            );
        }
    }

    /**
     * @param  array<string, \App\Models\Screen>  $screens
     * @param  array<string, \App\Models\Coach>  $coaches
     */
    private function seedPrograms(array $screens, array $coaches): void
    {
        $this->seedProgramSeries(
            $screens['atlas-cycle'],
            [
                $this->coachLabel($coaches['sarah-idrissi']),
                $this->coachLabel($coaches['karim-bennani']),
                $this->coachLabel($coaches['yassine-amrani']),
            ],
            [
                ['07:15', 'Morning Ride', 'Cycling', 45, 'Studio Velocity'],
                ['12:30', 'Core Express', 'Renforcement', 40, 'Studio Velocity'],
                ['18:30', 'Power Ride', 'Cycling', 50, 'Studio Velocity'],
            ]
        );

        $this->seedProgramSeries(
            $screens['rabat-cross'],
            [
                $this->coachLabel($coaches['mehdi-alaoui']),
                $this->coachLabel($coaches['salma-tazi']),
                $this->coachLabel($coaches['leila-cherkaoui']),
            ],
            [
                ['08:00', 'Bootcamp 360', 'HIIT', 50, 'Zone Functional'],
                ['13:00', 'Lunch Burn', 'Circuit', 40, 'Zone Functional'],
                ['19:00', 'Boxing Flow', 'Boxing', 55, 'Zone Functional'],
            ]
        );

        $this->seedProgramSeries(
            $screens['marrakech-lounge'],
            [
                $this->coachLabel($coaches['nabil-berrada']),
                $this->coachLabel($coaches['ines-othmani']),
                $this->coachLabel($coaches['omar-ziani']),
            ],
            [
                ['09:00', 'Mobility Reset', 'Mobilite', 35, 'Wellness Studio'],
                ['17:30', 'Functional Flow', 'Functional', 45, 'Wellness Studio'],
                ['20:00', 'Evening Calm', 'Recovery', 30, 'Wellness Studio'],
            ]
        );
    }

    /**
     * @param  list<string>  $coachNames
     * @param  list<array{0:string,1:string,2:string,3:int,4:string}>  $templates
     */
    private function seedProgramSeries(Screen $screen, array $coachNames, array $templates): void
    {
        foreach (Program::DAYS as $dayIndex => $day) {
            foreach ($templates as $slotIndex => [$startTime, $title, $courseType, $duration, $room]) {
                $coach = $coachNames[($dayIndex + $slotIndex) % count($coachNames)];
                $start = CarbonImmutable::createFromFormat('H:i', $startTime);
                $end = $start->addMinutes($duration)->format('H:i:s');

                Program::updateOrCreate(
                    [
                        'screen_id' => $screen->id,
                        'day' => $day,
                        'start_time' => $start->format('H:i:s'),
                        'room' => $room,
                    ],
                    [
                        'title' => $title,
                        'course_type' => $courseType,
                        'end_time' => $end,
                        'duration' => $duration,
                        'coach' => $coach,
                        'display_order' => $slotIndex + 1,
                        'is_active' => true,
                    ]
                );
            }
        }
    }

    private function coachLabel(Coach $coach): string
    {
        return trim(implode(' ', array_filter([$coach->first_name, $coach->name])));
    }

    private function buildPosterSvg(string $title, string $subtitle, string $kicker, string $accent): string
    {
        $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $subtitle = htmlspecialchars($subtitle, ENT_QUOTES, 'UTF-8');
        $kicker = htmlspecialchars($kicker, ENT_QUOTES, 'UTF-8');
        $accent = htmlspecialchars($accent, ENT_QUOTES, 'UTF-8');

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="1600" height="900" viewBox="0 0 1600 900">
  <defs>
    <linearGradient id="bg" x1="0%" x2="100%" y1="0%" y2="100%">
      <stop offset="0%" stop-color="#050505"/>
      <stop offset="55%" stop-color="#111111"/>
      <stop offset="100%" stop-color="{$accent}"/>
    </linearGradient>
  </defs>
  <rect width="1600" height="900" fill="url(#bg)"/>
  <circle cx="1320" cy="160" r="220" fill="rgba(255,255,255,0.06)"/>
  <circle cx="260" cy="760" r="180" fill="rgba(255,255,255,0.04)"/>
  <rect x="90" y="90" width="220" height="10" rx="5" fill="{$accent}"/>
  <text x="90" y="180" fill="#F7F2E7" font-family="Arial, Helvetica, sans-serif" font-size="42" letter-spacing="5">{$kicker}</text>
  <text x="90" y="340" fill="#FFFFFF" font-family="Arial, Helvetica, sans-serif" font-size="108" font-weight="700">{$title}</text>
  <text x="90" y="430" fill="#F0DFA8" font-family="Arial, Helvetica, sans-serif" font-size="40">{$subtitle}</text>
  <rect x="90" y="710" width="330" height="84" rx="18" fill="rgba(0,0,0,0.38)" stroke="{$accent}" stroke-width="3"/>
  <text x="125" y="765" fill="#FFFFFF" font-family="Arial, Helvetica, sans-serif" font-size="34">Demo club display</text>
</svg>
SVG;
    }
}

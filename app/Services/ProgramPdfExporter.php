<?php

namespace App\Services;

use App\Models\Program;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use RuntimeException;
use setasign\Fpdi\Fpdi;

class ProgramPdfExporter
{
    private const HEADER_TEXT_COLOR = [255, 255, 255];

    private const BODY_TEXT_COLOR = [28, 28, 28];

    private const BODY_FILL_COLOR = [255, 255, 255];

    private const BODY_BORDER_COLOR = [191, 24, 24];

    /**
     * @param  Collection<int, Program>  $programs
     */
    public function render(Collection $programs, array $filters = []): string
    {
        $templatePath = (string) config('programs.pdf_template');

        if ($templatePath === '' || ! is_file($templatePath)) {
            throw new RuntimeException("Le template PDF est introuvable: {$templatePath}");
        }

        $pdf = new Fpdi();
        $templatePageId = $pdf->setSourceFile($templatePath);
        $importedPage = $pdf->importPage(min($templatePageId, 1));
        $pageSize = $pdf->getTemplateSize($importedPage);

        foreach ($this->buildDayPages($programs, $filters) as $page) {
            /** @var Collection<int, Program> $items */
            $items = $page['items'];
            $chunks = $items->chunk($this->rowsPerPage())->values();

            if ($chunks->isEmpty()) {
                $chunks = collect([collect()]);
            }

            foreach ($chunks as $chunkIndex => $chunk) {
                $pdf->AddPage($pageSize['orientation'], [$pageSize['width'], $pageSize['height']]);
                $pdf->useTemplate($importedPage, 0, 0, $pageSize['width'], $pageSize['height']);

                $this->renderPageHeader(
                    $pdf,
                    (string) $page['label'],
                    (int) $items->count(),
                    $chunkIndex + 1,
                    $chunks->count(),
                    $pageSize['width']
                );

                if ($chunk->isEmpty()) {
                    $this->renderEmptyState($pdf, $pageSize['width']);

                    continue;
                }

                $this->renderRows($pdf, $chunk);
            }
        }

        return $pdf->Output('S');
    }

    /**
     * @param  Collection<int, Program>  $programs
     * @return Collection<int, array{label: string, items: Collection<int, Program>}>
     */
    private function buildDayPages(Collection $programs, array $filters): Collection
    {
        $requestedDay = trim((string) ($filters['day'] ?? ''));

        if ($requestedDay !== '') {
            return collect([
                [
                    'label' => $this->dayLabel($requestedDay),
                    'items' => $programs
                        ->filter(fn (Program $program): bool => $program->day === $requestedDay)
                        ->values(),
                ],
            ]);
        }

        $grouped = collect(Program::DAYS)
            ->map(function (string $day) use ($programs): array {
                return [
                    'label' => $this->dayLabel($day),
                    'items' => $programs
                        ->filter(fn (Program $program): bool => $program->day === $day)
                        ->values(),
                ];
            })
            ->filter(fn (array $page): bool => $page['items']->isNotEmpty())
            ->values();

        if ($grouped->isNotEmpty()) {
            return $grouped;
        }

        return collect([
            [
                'label' => 'Planning general',
                'items' => collect(),
            ],
        ]);
    }

    private function renderPageHeader(Fpdi $pdf, string $label, int $count, int $pageNumber, int $totalPages, float $pageWidth): void
    {
        [$r, $g, $b] = self::HEADER_TEXT_COLOR;
        $pdf->SetTextColor($r, $g, $b);

        $pdf->SetFont('Helvetica', 'B', 13);
        $pdf->SetXY(43, 22.5);
        $pdf->Cell(65, 8, $this->text($label), 0, 0, 'L');

        $summary = $count === 0
            ? 'Aucun programme'
            : $count.' programme'.($count > 1 ? 's' : '');

        if ($totalPages > 1) {
            $summary .= " | page {$pageNumber}/{$totalPages}";
        }

        $pdf->SetFont('Helvetica', '', 9);
        $pdf->SetXY($pageWidth - 74, 22.8);
        $pdf->Cell(58, 7, $this->text($summary), 0, 0, 'R');

        $pdf->SetXY(24, 70.5);
        $pdf->SetFont('Helvetica', '', 9);
        $pdf->Cell($pageWidth - 48, 5, $this->text('Export du planning des programmes'), 0, 0, 'L');
    }

    /**
     * @param  Collection<int, Program>  $programs
     */
    private function renderRows(Fpdi $pdf, Collection $programs): void
    {
        $startY = 95.5;
        $rowHeight = 14.2;
        $rowWidth = 163.5;

        foreach ($programs->values() as $index => $program) {
            $y = $startY + ($index * $rowHeight);

            $pdf->SetDrawColor(...self::BODY_BORDER_COLOR);
            $pdf->SetFillColor(...self::BODY_FILL_COLOR);
            $pdf->Rect(23.5, $y, $rowWidth, 11.8, 'FD');

            $pdf->SetTextColor(...self::BODY_TEXT_COLOR);

            $this->drawColumn(
                $pdf,
                26,
                $y + 1.6,
                70,
                $this->programTitle($program),
                $this->programSubtitle($program),
                true
            );

            $this->drawColumn(
                $pdf,
                101,
                $y + 1.6,
                30,
                $this->timeLabel($program),
                $this->durationLabel($program)
            );

            $this->drawColumn(
                $pdf,
                136,
                $y + 1.6,
                47,
                $this->coachAndRoomLabel($program),
                $this->screenAndStatusLabel($program)
            );
        }
    }

    private function renderEmptyState(Fpdi $pdf, float $pageWidth): void
    {
        $pdf->SetFillColor(...self::BODY_FILL_COLOR);
        $pdf->SetDrawColor(...self::BODY_BORDER_COLOR);
        $pdf->Rect(23.5, 104, $pageWidth - 47, 28, 'FD');

        $pdf->SetTextColor(...self::BODY_TEXT_COLOR);
        $pdf->SetFont('Helvetica', 'B', 12);
        $pdf->SetXY(23.5, 112);
        $pdf->Cell($pageWidth - 47, 6, $this->text('Aucun programme pour cette selection.'), 0, 1, 'C');

        $pdf->SetFont('Helvetica', '', 9);
        $pdf->SetX(23.5);
        $pdf->Cell($pageWidth - 47, 5, $this->text('Ajoute des cours dans le module Programmes puis relance l export.'), 0, 0, 'C');
    }

    private function drawColumn(
        Fpdi $pdf,
        float $x,
        float $y,
        float $width,
        string $title,
        string $subtitle,
        bool $highlight = false
    ): void {
        $pdf->SetXY($x, $y);
        $pdf->SetFont('Helvetica', $highlight ? 'B' : '', $highlight ? 10 : 9.5);
        $pdf->Cell($width, 4.3, $this->text($title, $highlight ? 44 : 28), 0, 2);

        $pdf->SetX($x);
        $pdf->SetFont('Helvetica', '', 8);
        $pdf->Cell($width, 3.9, $this->text($subtitle, 46), 0, 0);
    }

    private function programTitle(Program $program): string
    {
        return $program->title ?: 'Programme';
    }

    private function programSubtitle(Program $program): string
    {
        return $program->course_type ?: 'cours libre';
    }

    private function timeLabel(Program $program): string
    {
        return $this->formatTime($program->start_time).' - '.$this->formatTime($program->computed_end_time ?? $program->end_time);
    }

    private function durationLabel(Program $program): string
    {
        return ((int) $program->duration).' min';
    }

    private function coachAndRoomLabel(Program $program): string
    {
        return trim(($program->coach ?: '-') . ' / ' . ($program->room ?: '-'));
    }

    private function screenAndStatusLabel(Program $program): string
    {
        $parts = [];

        if ($program->screen?->name) {
            $parts[] = $program->screen->name;
        }

        $parts[] = $program->is_active ? 'Actif' : 'Inactif';

        return implode(' | ', $parts);
    }

    private function dayLabel(string $day): string
    {
        return match ($day) {
            'lundi' => 'Lundi',
            'mardi' => 'Mardi',
            'mercredi' => 'Mercredi',
            'jeudi' => 'Jeudi',
            'vendredi' => 'Vendredi',
            'samedi' => 'Samedi',
            'dimanche' => 'Dimanche',
            default => ucfirst($day),
        };
    }

    private function formatTime(?string $time): string
    {
        if ($time === null || trim($time) === '') {
            return '--:--';
        }

        if (strlen($time) >= 5) {
            return substr($time, 0, 5);
        }

        try {
            return Carbon::parse($time)->format('H:i');
        } catch (\Throwable) {
            return $time;
        }
    }

    private function rowsPerPage(): int
    {
        return max(1, (int) config('programs.pdf_rows_per_page', 10));
    }

    private function text(string $value, int $maxLength = 0): string
    {
        $normalized = trim(preg_replace('/\s+/', ' ', $value)) ?: '-';

        if ($maxLength > 0 && mb_strlen($normalized) > $maxLength) {
            $normalized = rtrim(mb_substr($normalized, 0, $maxLength - 3)).'...';
        }

        return iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $normalized) ?: '';
    }
}

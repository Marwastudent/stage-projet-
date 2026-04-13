<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('programs', function (Blueprint $table): void {
            $table->string('title', 150)->default('Programme')->after('id');
            $table->string('course_type', 100)->nullable()->after('title');
            $table->unsignedSmallInteger('duration')->default(60)->after('start_time');
            $table->unsignedBigInteger('screen_id')->nullable()->after('room');
            $table->unsignedInteger('display_order')->default(1)->after('screen_id');
            $table->boolean('is_active')->default(true)->after('display_order');

            $table->index(['day', 'start_time']);
            $table->index('coach');
            $table->index('room');
            $table->index(['screen_id', 'is_active']);
        });

        $programs = DB::table('programs')
            ->select('id', 'start_time', 'end_time', 'coach', 'room')
            ->get();

        foreach ($programs as $program) {
            $duration = $this->resolveDuration($program->start_time, $program->end_time);
            $titleSource = trim(implode(' - ', array_filter([$program->coach, $program->room])));

            DB::table('programs')
                ->where('id', $program->id)
                ->update([
                    'title' => Str::limit($titleSource !== '' ? $titleSource : 'Programme', 150, ''),
                    'duration' => $duration,
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table): void {
            $table->dropIndex(['day', 'start_time']);
            $table->dropIndex(['coach']);
            $table->dropIndex(['room']);
            $table->dropIndex(['screen_id', 'is_active']);
            $table->dropColumn([
                'title',
                'course_type',
                'duration',
                'screen_id',
                'display_order',
                'is_active',
            ]);
        });
    }

    private function resolveDuration(?string $startTime, ?string $endTime): int
    {
        if (! $startTime || ! $endTime) {
            return 60;
        }

        try {
            $start = $this->parseTime($startTime);
            $end = $this->parseTime($endTime);
        } catch (\Throwable) {
            return 60;
        }

        $minutes = $start->diffInMinutes($end, false);

        return $minutes > 0 ? (int) $minutes : 60;
    }

    private function parseTime(string $value): Carbon
    {
        $normalized = strlen($value) === 5 ? $value : substr($value, 0, 8);
        $format = strlen($normalized) === 5 ? 'H:i' : 'H:i:s';

        return Carbon::createFromFormat($format, $normalized);
    }
};

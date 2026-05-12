<?php

namespace App\Services\Client;

use App\Models\DictationHistory;
use App\Models\Lesson;
use App\Models\MockExam;
use App\Models\User;
use Illuminate\Support\Collection;

class ClientDashboardService
{
    /**
     * Get dashboard statistics for the authenticated client user.
     */
    public function getStats(User $user): array
    {
        $dictations = $user->dictationHistories();
        $mockExams = $user->mockExams();
        $completedMockExams = $user->mockExams()->where('status', MockExam::STATUS_COMPLETED);

        return [
            'total_dictations' => $dictations->count(),
            'avg_wpm' => round($dictations->avg('wpm') ?? 0),
            'avg_accuracy' => round($dictations->avg('accuracy') ?? 0, 1),
            'total_mock_exams' => $mockExams->count(),
            'completed_mock_exams' => $completedMockExams->count(),
            'avg_band' => round($completedMockExams->avg('overall_band') ?? 0, 1),
        ];
    }

    /**
     * Get rich analytics for dashboard charts and KPI blocks.
     */
    public function getAnalytics(User $user, int $periodDays = 30): array
    {
        $periodDays = in_array($periodDays, [7, 30, 90, 180, 365], true) ? $periodDays : 30;
        $periodStart = now()->subDays(max(0, $periodDays - 1))->startOfDay();

        $mockExams = $user->mockExams()
            ->with('lesson:id,title,task_type,sample_essay')
            ->where('submitted_at', '>=', $periodStart)
            ->latest('submitted_at')
            ->get();

        $dictationHistories = $user->dictationHistories()
            ->with('lesson:id,title,task_type,sample_essay')
            ->where('completed_at', '>=', $periodStart)
            ->latest('completed_at')
            ->get();

        $completedMockExams = $mockExams
            ->where('status', MockExam::STATUS_COMPLETED)
            ->values();

        return [
            'period_days' => $periodDays,
            'summary' => $this->buildSummary($completedMockExams, $dictationHistories, $mockExams),
            'charts' => $this->buildCharts($completedMockExams, $dictationHistories, $mockExams, $periodDays),
        ];
    }

    /**
     * Get WPM data for the last 7 days.
     */
    public function getWeeklyWpm(User $user): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $avg = DictationHistory::where('user_id', $user->id)
                ->whereDate('completed_at', $date->toDateString())
                ->avg('wpm');
            $data[] = [
                'date' => $date->format('D'),
                'label' => $date->format('d/m'),
                'wpm' => round($avg ?? 0),
            ];
        }

        return $data;
    }

    /**
     * Get recent activity feed (dictations + mock exams combined).
     */
    public function getRecentActivity(User $user, int $limit = 10): Collection
    {
        $dictations = $user->dictationHistories()
            ->with('lesson:id,title,task_type')
            ->latest('completed_at')
            ->take($limit)
            ->get()
            ->map(fn ($d) => [
                'type' => 'dictation',
                'history_id' => $d->id,
                'lesson_id' => $d->lesson_id,
                'lesson' => $d->lesson,
                'wpm' => $d->wpm,
                'accuracy' => $d->accuracy,
                'created_at' => $d->completed_at,
            ]);

        $exams = $user->mockExams()
            ->with('lesson:id,title,task_type')
            ->latest('submitted_at')
            ->take($limit)
            ->get()
            ->map(fn ($e) => [
                'type' => 'mock_exam',
                'exam_id' => $e->id,
                'lesson_id' => $e->lesson_id,
                'lesson' => $e->lesson,
                'status' => $e->status,
                'band' => $e->overall_band,
                'created_at' => $e->submitted_at,
            ]);

        return $dictations->concat($exams)
            ->sortByDesc('created_at')
            ->take($limit)
            ->values();
    }

    /**
     * Get recommended lessons for the user.
     */
    public function getRecommendedLessons(User $user, int $limit = 3): Collection
    {
        $completedIds = $user->dictationHistories()->pluck('lesson_id')->unique();

        return Lesson::where('status', 'published')
            ->whereNotIn('id', $completedIds)
            ->inRandomOrder()
            ->take($limit)
            ->get();
    }

    private function buildSummary(Collection $completedMockExams, Collection $dictationHistories, Collection $allMockExams): array
    {
        $mockExamSeconds = (int) $completedMockExams->sum('time_taken_seconds');
        $dictationSeconds = (int) $dictationHistories->sum(
            fn (DictationHistory $history) => $this->estimateDictationDurationSeconds($history) ?? 0,
        );

        $latestActivityAt = collect([
            $allMockExams->max('submitted_at'),
            $dictationHistories->max('completed_at'),
        ])->filter()->sortDesc()->first();

        return [
            'total_attempts' => $allMockExams->count() + $dictationHistories->count(),
            'avg_band' => $this->averageOrNull($completedMockExams, 'overall_band'),
            'avg_tr' => $this->averageOrNull($completedMockExams, 'tr_score'),
            'avg_cc' => $this->averageOrNull($completedMockExams, 'cc_score'),
            'avg_lr' => $this->averageOrNull($completedMockExams, 'lr_score'),
            'avg_gra' => $this->averageOrNull($completedMockExams, 'gra_score'),
            'avg_wpm' => $this->averageOrNull($dictationHistories, 'wpm', 0),
            'avg_accuracy' => $this->averageOrNull($dictationHistories, 'accuracy', 2),
            'mock_exam_time_label' => $this->formatDuration($mockExamSeconds),
            'dictation_time_label' => $this->formatDuration($dictationSeconds),
            'total_learning_time_label' => $this->formatDuration($mockExamSeconds + $dictationSeconds),
            'latest_activity_at' => $latestActivityAt,
        ];
    }

    private function buildCharts(
        Collection $completedMockExams,
        Collection $dictationHistories,
        Collection $allMockExams,
        int $periodDays,
    ): array {
        $mockTrend = $completedMockExams
            ->sortByDesc('submitted_at')
            ->take(12)
            ->sortBy('submitted_at')
            ->values();

        $dictationTrend = $dictationHistories
            ->sortByDesc('completed_at')
            ->take(12)
            ->sortBy('completed_at')
            ->values();

        $typingScatter = $dictationHistories
            ->sortByDesc('completed_at')
            ->take(30)
            ->sortBy('completed_at')
            ->values();

        $mockByDate = $allMockExams->groupBy(
            fn (MockExam $exam) => optional($exam->submitted_at)->format('Y-m-d'),
        );
        $dictationByDate = $dictationHistories->groupBy(
            fn (DictationHistory $history) => optional($history->completed_at)->format('Y-m-d'),
        );

        $volumeLabels = [];
        $mockVolumes = [];
        $dictationVolumes = [];
        $mockMinutes = [];
        $dictationMinutes = [];

        $volumeWindowDays = min(30, max(7, $periodDays));

        for ($index = $volumeWindowDays - 1; $index >= 0; $index--) {
            $date = now()->subDays($index);
            $dateKey = $date->format('Y-m-d');
            $dailyMockExams = $mockByDate->get($dateKey, collect());
            $dailyDictations = $dictationByDate->get($dateKey, collect());

            $volumeLabels[] = $date->format('d/m');
            $mockVolumes[] = $dailyMockExams->count();
            $dictationVolumes[] = $dailyDictations->count();
            $mockMinutes[] = round(((int) $dailyMockExams->sum('time_taken_seconds')) / 60, 1);
            $dictationMinutes[] = round(((int) $dailyDictations->sum(
                fn (DictationHistory $history) => $this->estimateDictationDurationSeconds($history) ?? 0,
            )) / 60, 1);
        }

        $avgTr = $this->averageOrNull($completedMockExams, 'tr_score');
        $avgCc = $this->averageOrNull($completedMockExams, 'cc_score');
        $avgLr = $this->averageOrNull($completedMockExams, 'lr_score');
        $avgGra = $this->averageOrNull($completedMockExams, 'gra_score');
        $scoreValues = [$avgTr, $avgCc, $avgLr, $avgGra];

        return [
            'mock_exam_band_trend' => [
                'labels' => $mockTrend->map(fn (MockExam $exam) => optional($exam->submitted_at)->format('d/m'))->all(),
                'datasets' => [
                    'overall' => $mockTrend->map(fn (MockExam $exam) => $this->roundMaybe($exam->overall_band))->all(),
                    'tr' => $mockTrend->map(fn (MockExam $exam) => $this->roundMaybe($exam->tr_score))->all(),
                    'cc' => $mockTrend->map(fn (MockExam $exam) => $this->roundMaybe($exam->cc_score))->all(),
                    'lr' => $mockTrend->map(fn (MockExam $exam) => $this->roundMaybe($exam->lr_score))->all(),
                    'gra' => $mockTrend->map(fn (MockExam $exam) => $this->roundMaybe($exam->gra_score))->all(),
                ],
            ],
            'dictation_trend' => [
                'labels' => $dictationTrend->map(fn (DictationHistory $history) => optional($history->completed_at)->format('d/m'))->all(),
                'datasets' => [
                    'wpm' => $dictationTrend->map(fn (DictationHistory $history) => (int) $history->wpm)->all(),
                    'accuracy' => $dictationTrend->map(fn (DictationHistory $history) => $this->roundMaybe($history->accuracy, 2))->all(),
                ],
            ],
            'attempt_volume' => [
                'labels' => $volumeLabels,
                'mock_exam' => $mockVolumes,
                'dictation' => $dictationVolumes,
                'mock_exam_minutes' => $mockMinutes,
                'dictation_minutes' => $dictationMinutes,
                'window_days' => $volumeWindowDays,
            ],
            'score_radar' => [
                'labels' => ['TR', 'CC', 'LR', 'GRA'],
                'values' => array_map(fn ($value) => $value ?? 0, $scoreValues),
                'target' => [7, 7, 7, 7],
                'has_data' => collect($scoreValues)->filter(fn ($value) => $value !== null)->isNotEmpty(),
            ],
            'typing_scatter' => [
                'labels' => $typingScatter->map(fn (DictationHistory $history) => optional($history->completed_at)->format('d/m H:i'))->all(),
                'points' => $typingScatter->map(fn (DictationHistory $history) => [
                    'x' => (int) $history->wpm,
                    'y' => $this->roundMaybe($history->accuracy, 2),
                ])->all(),
            ],
        ];
    }

    private function estimateDictationDurationSeconds(DictationHistory $history): ?int
    {
        $wordCount = $history->lesson?->sample_essay
            ? str_word_count(strip_tags((string) $history->lesson->sample_essay))
            : 0;

        $wpm = (int) ($history->wpm ?? 0);

        if ($wordCount <= 0 || $wpm <= 0) {
            return null;
        }

        return (int) round(($wordCount / $wpm) * 60);
    }

    private function formatDuration(?int $seconds): ?string
    {
        if ($seconds === null || $seconds <= 0) {
            return null;
        }

        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $remainingSeconds = $seconds % 60;

        if ($hours > 0) {
            return sprintf('%dh %02dm %02ds', $hours, $minutes, $remainingSeconds);
        }

        if ($minutes > 0) {
            return sprintf('%dm %02ds', $minutes, $remainingSeconds);
        }

        return sprintf('%ds', $remainingSeconds);
    }

    private function averageOrNull(Collection $items, string $key, int $precision = 1): ?float
    {
        if ($items->isEmpty()) {
            return null;
        }

        return $this->roundMaybe($items->avg($key), $precision);
    }

    private function roundMaybe(float|int|string|null $value, int $precision = 1): ?float
    {
        if ($value === null || ! is_numeric($value)) {
            return null;
        }

        return round((float) $value, $precision);
    }
}

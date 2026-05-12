<?php

namespace App\Services;

use App\Models\DictationHistory;
use App\Models\MockExam;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as PaginationLengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ClientUserService
{
    public function getClients(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = User::query()->clients();

        if (! empty($filters['search'])) {
            $query->where(function ($builder) use ($filters): void {
                $builder->where('name', 'like', '%'.$filters['search'].'%')
                    ->orWhere('email', 'like', '%'.$filters['search'].'%');
            });
        }

        if (! empty($filters['subscription_tier'])) {
            $query->where('subscription_tier', $filters['subscription_tier']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate($perPage)->withQueryString();
    }

    public function getClientDetail(User $client, array $filters = []): array
    {
        abort_unless($client->role === 'user', 404);

        $filters = $this->normalizeFilters($filters);
        $periodStart = now()->subDays(max(0, $filters['period_days'] - 1))->startOfDay();

        $client = $client->load([
            'transactions' => fn ($q) => $q->with('plan')->latest()->limit(10),
        ]);

        $mockExams = $client->mockExams()
            ->with('lesson')
            ->where('submitted_at', '>=', $periodStart)
            ->latest('submitted_at')
            ->get();

        $dictationHistories = $client->dictationHistories()
            ->with('lesson')
            ->where('completed_at', '>=', $periodStart)
            ->latest('completed_at')
            ->get();

        $filteredMockExams = $filters['source'] === 'dictation'
            ? collect()
            : $mockExams;
        $filteredDictationHistories = $filters['source'] === 'mock_exam'
            ? collect()
            : $dictationHistories;

        $attempts = $this->buildAttempts($mockExams, $dictationHistories, $filters['source']);

        return [
            'client' => $client,
            'filters' => $filters,
            'summary' => $this->buildSummary($filteredMockExams, $filteredDictationHistories),
            'charts' => $this->buildCharts($filteredMockExams, $filteredDictationHistories, $filters['period_days']),
            'attempts' => $this->paginateAttempts($attempts, $filters['per_page'], $filters['page']),
        ];
    }

    public function updateStatus(User $client, string $status): User
    {
        abort_unless($client->role === 'user', 404);

        $client->update(['status' => $status]);

        return $client->fresh();
    }

    public function updateSubscription(User $client, string $subscriptionTier, ?string $subscriptionExpiresAt): User
    {
        abort_unless($client->role === 'user', 404);

        $client->update([
            'subscription_tier' => $subscriptionTier,
            'subscription_expires_at' => $subscriptionTier === 'pro' && $subscriptionExpiresAt
                ? Carbon::parse($subscriptionExpiresAt)->endOfDay()
                : null,
        ]);

        return $client->fresh();
    }

    private function normalizeFilters(array $filters): array
    {
        $periodDays = (int) ($filters['period_days'] ?? 30);
        $source = (string) ($filters['source'] ?? 'all');
        $perPage = (int) ($filters['per_page'] ?? 15);
        $page = (int) ($filters['page'] ?? request()->integer('page', 1));

        return [
            'period_days' => in_array($periodDays, [7, 30, 90, 180, 365], true) ? $periodDays : 30,
            'source' => in_array($source, ['all', 'mock_exam', 'dictation'], true) ? $source : 'all',
            'per_page' => in_array($perPage, [10, 15, 20, 50], true) ? $perPage : 15,
            'page' => max(1, $page),
        ];
    }

    private function buildSummary(Collection $mockExams, Collection $dictationHistories): array
    {
        $completedMockExams = $mockExams->where('status', MockExam::STATUS_COMPLETED)->values();
        $mockExamSeconds = (int) $completedMockExams->sum('time_taken_seconds');
        $dictationSeconds = (int) $dictationHistories->sum(fn (DictationHistory $history) => $this->estimateDictationDurationSeconds($history) ?? 0);

        $latestMockAt = $mockExams->max('submitted_at');
        $latestDictationAt = $dictationHistories->max('completed_at');
        $latestActivityAt = collect([$latestMockAt, $latestDictationAt])
            ->filter()
            ->map(fn ($date) => Carbon::parse($date))
            ->sortDesc()
            ->first();

        $totalLearningSeconds = $mockExamSeconds + $dictationSeconds;

        return [
            'total_mock_exams' => $mockExams->count(),
            'completed_mock_exams' => $completedMockExams->count(),
            'failed_mock_exams' => $mockExams->where('status', MockExam::STATUS_FAILED)->count(),
            'grading_mock_exams' => $mockExams->where('status', MockExam::STATUS_GRADING)->count(),
            'avg_overall_band' => $this->averageOrNull($completedMockExams, 'overall_band'),
            'avg_tr' => $this->averageOrNull($completedMockExams, 'tr_score'),
            'avg_cc' => $this->averageOrNull($completedMockExams, 'cc_score'),
            'avg_lr' => $this->averageOrNull($completedMockExams, 'lr_score'),
            'avg_gra' => $this->averageOrNull($completedMockExams, 'gra_score'),
            'total_dictations' => $dictationHistories->count(),
            'avg_wpm' => $this->averageOrNull($dictationHistories, 'wpm', 0),
            'avg_accuracy' => $this->averageOrNull($dictationHistories, 'accuracy', 2),
            'mock_exam_time_label' => $this->formatDuration($mockExamSeconds),
            'dictation_time_label' => $this->formatDuration($dictationSeconds),
            'total_learning_time_label' => $this->formatDuration($totalLearningSeconds),
            'latest_activity_at' => $latestActivityAt,
        ];
    }

    private function buildCharts(Collection $mockExams, Collection $dictationHistories, int $periodDays): array
    {
        $mockTrend = $mockExams
            ->where('status', MockExam::STATUS_COMPLETED)
            ->sortByDesc('submitted_at')
            ->take(12)
            ->sortBy('submitted_at')
            ->values();

        $dictationTrend = $dictationHistories
            ->sortByDesc('completed_at')
            ->take(12)
            ->sortBy('completed_at')
            ->values();

        $volumeLabels = [];
        $mockVolumes = [];
        $dictationVolumes = [];
        $mockMinutes = [];
        $dictationMinutes = [];

        $mockByDate = $mockExams->groupBy(fn (MockExam $exam) => optional($exam->submitted_at)->format('Y-m-d'));
        $dictationByDate = $dictationHistories->groupBy(fn (DictationHistory $history) => optional($history->completed_at)->format('Y-m-d'));

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
            $dictationMinutes[] = round(((int) $dailyDictations->sum(fn (DictationHistory $history) => $this->estimateDictationDurationSeconds($history) ?? 0)) / 60, 1);
        }

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
        ];
    }

    private function buildAttempts(Collection $mockExams, Collection $dictationHistories, string $source): Collection
    {
        $mockAttempts = $mockExams->map(function (MockExam $exam): array {
            $doneAt = $exam->submitted_at;
            $durationSeconds = $exam->time_taken_seconds ? (int) $exam->time_taken_seconds : null;

            return [
                'row_key' => 'mock_exam-'.$exam->id,
                'source' => 'mock_exam',
                'source_label' => 'Mock Exam',
                'attempt_id' => $exam->id,
                'lesson_title' => $exam->lesson?->title ?? 'N/A',
                'lesson_task' => $exam->lesson?->task_type,
                'done_at' => $doneAt,
                'done_at_label' => $doneAt ? $doneAt->format('d/m/Y H:i') : '-',
                'done_timestamp' => $doneAt?->timestamp ?? 0,
                'duration_seconds' => $durationSeconds,
                'duration_label' => $this->formatDuration($durationSeconds),
                'status' => $exam->status,
                'word_count' => $exam->word_count,
                'wpm' => null,
                'accuracy' => null,
                'scores' => [
                    'overall_band' => $this->roundMaybe($exam->overall_band),
                    'tr' => $this->roundMaybe($exam->tr_score),
                    'cc' => $this->roundMaybe($exam->cc_score),
                    'lr' => $this->roundMaybe($exam->lr_score),
                    'gra' => $this->roundMaybe($exam->gra_score),
                ],
                'essay_preview' => Str::limit(trim((string) $exam->user_essay), 260),
                'feedback_summary' => data_get($exam->ai_feedback, 'overall_feedback'),
            ];
        });

        $dictationAttempts = $dictationHistories->map(function (DictationHistory $history): array {
            $doneAt = $history->completed_at;
            $durationSeconds = $this->estimateDictationDurationSeconds($history);
            $wordCount = $history->lesson?->sample_essay
                ? str_word_count(strip_tags((string) $history->lesson->sample_essay))
                : null;

            return [
                'row_key' => 'dictation-'.$history->id,
                'source' => 'dictation',
                'source_label' => 'Dictation',
                'attempt_id' => $history->id,
                'lesson_title' => $history->lesson?->title ?? 'N/A',
                'lesson_task' => $history->lesson?->task_type,
                'done_at' => $doneAt,
                'done_at_label' => $doneAt ? $doneAt->format('d/m/Y H:i') : '-',
                'done_timestamp' => $doneAt?->timestamp ?? 0,
                'duration_seconds' => $durationSeconds,
                'duration_label' => $this->formatDuration($durationSeconds),
                'status' => MockExam::STATUS_COMPLETED,
                'word_count' => $wordCount,
                'wpm' => (int) $history->wpm,
                'accuracy' => $this->roundMaybe($history->accuracy, 2),
                'scores' => [
                    'overall_band' => null,
                    'tr' => null,
                    'cc' => null,
                    'lr' => null,
                    'gra' => null,
                ],
                'essay_preview' => null,
                'feedback_summary' => null,
            ];
        });

        $attempts = $mockAttempts->concat($dictationAttempts);

        if ($source !== 'all') {
            $attempts = $attempts->where('source', $source);
        }

        return $attempts
            ->sortByDesc('done_timestamp')
            ->values();
    }

    private function paginateAttempts(Collection $attempts, int $perPage, int $page): LengthAwarePaginator
    {
        $total = $attempts->count();
        $items = $attempts->forPage($page, $perPage)->values();

        return new PaginationLengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ],
        );
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

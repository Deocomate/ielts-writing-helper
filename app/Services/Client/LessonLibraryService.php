<?php

namespace App\Services\Client;

use App\Models\Lesson;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class LessonLibraryService
{
    /**
     * Get published lessons with filters for the lesson library.
     */
    public function getLessons(array $filters, int $perPage = 12): LengthAwarePaginator
    {
        $query = Lesson::where('status', 'published');

        $taskType = $filters['task_type'] ?? null;
        if ($taskType === 'Task 1') {
            $taskType = 'task_1';
        }
        if ($taskType === 'Task 2') {
            $taskType = 'task_2';
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('prompt_text', 'like', "%{$search}%");
            });
        }

        if (!empty($taskType)) {
            $query->where('task_type', $taskType);
        }

        if (!empty($filters['question_type'])) {
            $query->where('question_type', $filters['question_type']);
        }

        if (array_key_exists('band_min', $filters) && $filters['band_min'] !== null && $filters['band_min'] !== '') {
            $query->where('band_score', '>=', $filters['band_min']);
        }

        if (!empty($filters['access'])) {
            $query->where('is_premium', in_array($filters['access'], ['pro', 'premium'], true));
        }

        $sort = $filters['sort'] ?? 'latest';
        match ($sort) {
            'band_high', 'band_desc' => $query->orderByDesc('band_score'),
            'band_low', 'band_asc'   => $query->orderBy('band_score'),
            default      => $query->latest(),
        };

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Get a published lesson by ID with its annotations and vocabularies.
     */
    public function getLesson(int $id): Lesson
    {
        return Lesson::where('status', 'published')
            ->with(['annotations', 'vocabularies'])
            ->findOrFail($id);
    }

    /**
     * Get total count of published lessons.
     */
    public function getTotalCount(): int
    {
        return Lesson::where('status', 'published')->count();
    }
}

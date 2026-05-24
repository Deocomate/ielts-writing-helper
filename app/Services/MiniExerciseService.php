<?php

namespace App\Services;

use App\Models\MiniExercise;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MiniExerciseService
{
    public function getExercises(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $query = MiniExercise::query();

        if (! empty($filters['published_only'])) {
            $query->where('status', 'published');
        }

        if (! empty($filters['search'])) {
            $query->where('title', 'like', '%'.$filters['search'].'%');
        }

        if (! empty($filters['mistake_type'])) {
            $query->where('mistake_type', $filters['mistake_type']);
        }

        if (! empty($filters['exercise_type'])) {
            $query->where('exercise_type', $filters['exercise_type']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate($perPage)->withQueryString();
    }

    public function createExercise(array $data): MiniExercise
    {
        return MiniExercise::query()->create($this->normalizePayload($data));
    }

    public function updateExercise(MiniExercise $exercise, array $data): MiniExercise
    {
        $exercise->update($this->normalizePayload($data, $exercise));

        return $exercise->fresh();
    }

    public function getPublishedBySlug(string $slug): MiniExercise
    {
        return MiniExercise::query()
            ->where('status', 'published')
            ->where('slug', $slug)
            ->firstOrFail();
    }

    private function normalizePayload(array $data, ?MiniExercise $exercise = null): array
    {
        $exerciseType = $data['exercise_type'];
        $data['slug'] = $this->uniqueSlug($data['title'], $exercise?->id);
        $data['question_data'] = match ($exerciseType) {
            MiniExercise::TYPE_FILL_BLANK => $this->buildBlankPayload($data, false),
            MiniExercise::TYPE_DRAG_DROP => $this->buildBlankPayload($data, true),
            MiniExercise::TYPE_SHORT_ANSWER => $this->buildShortAnswerPayload($data),
            default => throw ValidationException::withMessages(['exercise_type' => 'Loại bài tập không hợp lệ.']),
        };

        return Arr::only($data, [
            'title',
            'slug',
            'mistake_type',
            'exercise_type',
            'difficulty_level',
            'question_data',
            'explanation',
            'status',
        ]);
    }

    private function buildBlankPayload(array $data, bool $allowMultipleBlanks): array
    {
        $sentence = trim((string) ($data['marked_sentence'] ?? ''));
        preg_match_all('/\[([^\]]+)\]/u', $sentence, $matches);
        $answers = collect($matches[1] ?? [])
            ->map(fn (string $answer): string => trim($answer))
            ->filter()
            ->values()
            ->all();

        if (empty($answers)) {
            throw ValidationException::withMessages([
                'marked_sentence' => 'Câu hỏi cần có ít nhất một đáp án trong dấu [ ].',
            ]);
        }

        if (! $allowMultipleBlanks && count($answers) !== 1) {
            throw ValidationException::withMessages([
                'marked_sentence' => 'Dạng điền từ chỉ hỗ trợ một ô trống trong bản này.',
            ]);
        }

        $displaySentence = preg_replace('/\[([^\]]+)\]/u', '___', $sentence);
        $distractors = $this->splitCsv($data['distractors'] ?? '');
        $options = collect(array_merge($answers, $distractors))
            ->unique(fn (string $option): string => mb_strtolower($option))
            ->shuffle()
            ->values()
            ->all();

        return [
            'marked_sentence' => $sentence,
            'sentence' => $displaySentence,
            'answers' => $answers,
            'options' => $options,
        ];
    }

    private function buildShortAnswerPayload(array $data): array
    {
        $answers = $this->splitCsv($data['accepted_answers'] ?? '');

        if (empty($answers)) {
            throw ValidationException::withMessages([
                'accepted_answers' => 'Vui lòng nhập ít nhất một đáp án đúng.',
            ]);
        }

        return [
            'prompt' => trim((string) ($data['prompt'] ?? '')),
            'answers' => $answers,
        ];
    }

    /**
     * @return array<int, string>
     */
    private function splitCsv(mixed $value): array
    {
        return collect(explode(',', (string) $value))
            ->map(fn (string $item): string => trim($item))
            ->filter()
            ->values()
            ->all();
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug !== '' ? $baseSlug : Str::random(8);
        $counter = 2;

        while (MiniExercise::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}

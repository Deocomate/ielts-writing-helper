<?php

namespace App\Services\Client;

use App\Models\User;
use App\Models\UserVocabulary;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class VocabularyService
{
    /**
     * Get user's saved vocabulary with optional filters.
     */
    public function getVocabularies(User $user, array $filters, int $perPage = 12): LengthAwarePaginator
    {
        $query = $user->userVocabularies()->latest();

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('word', 'like', "%{$search}%")
                  ->orWhere('meaning', 'like', "%{$search}%");
            });
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Save a new vocabulary word for the user.
     */
    public function saveVocabulary(User $user, array $data): UserVocabulary
    {
        $word = trim($data['word']);
        $meaning = trim((string) ($data['meaning'] ?? ''));

        if ($meaning === '') {
            $meaning = 'Chưa có nghĩa';
        }

        $vocabulary = UserVocabulary::firstOrCreate(
            [
                'user_id' => $user->id,
                'word'    => $word,
            ],
            [
                'lesson_id'        => $data['lesson_id'] ?? null,
                'meaning'          => $meaning,
                'context_sentence' => $data['context_sentence'] ?? null,
            ]
        );

        if (! $vocabulary->wasRecentlyCreated) {
            $updates = [];

            if ($this->shouldReplaceMeaning($vocabulary->meaning, $meaning)) {
                $updates['meaning'] = $meaning;
            }

            if (! $vocabulary->lesson_id && ! empty($data['lesson_id'])) {
                $updates['lesson_id'] = $data['lesson_id'];
            }

            if (! $vocabulary->context_sentence && ! empty($data['context_sentence'])) {
                $updates['context_sentence'] = $data['context_sentence'];
            }

            if (! empty($updates)) {
                $vocabulary->fill($updates);
                $vocabulary->save();
            }
        }

        return $vocabulary;
    }

    /**
     * Delete a vocabulary word.
     */
    public function deleteVocabulary(User $user, int $vocabularyId): void
    {
        $vocab = UserVocabulary::where('user_id', $user->id)->findOrFail($vocabularyId);
        $vocab->delete();
    }

    /**
     * Get total vocabulary count for user.
     */
    public function getCount(User $user): int
    {
        return $user->userVocabularies()->count();
    }

    private function shouldReplaceMeaning(string $currentMeaning, string $newMeaning): bool
    {
        $normalizedCurrentMeaning = trim($currentMeaning);
        $normalizedNewMeaning = trim($newMeaning);

        if ($normalizedNewMeaning === '' || $normalizedCurrentMeaning === $normalizedNewMeaning) {
            return false;
        }

        return in_array($normalizedCurrentMeaning, ['', 'Chưa có nghĩa'], true);
    }
}

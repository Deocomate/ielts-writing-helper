<?php

namespace App\Services\Client;

use App\Models\DictationHistory;
use App\Models\Lesson;
use App\Models\User;

class DictationService
{
    /**
     * Get lesson data prepared for dictation mode.
     */
    public function getLessonForDictation(int $lessonId): Lesson
    {
        return Lesson::where('status', 'published')
            ->with('vocabularies')
            ->findOrFail($lessonId);
    }

    /**
     * Get a specific dictation history result of the authenticated user.
     */
    public function getResult(int $historyId, User $user): DictationHistory
    {
        return DictationHistory::query()
            ->with('lesson:id,title,task_type,question_type,band_score')
            ->where('user_id', $user->id)
            ->findOrFail($historyId);
    }

    /**
     * Save a completed dictation result.
     */
    public function saveResult(User $user, array $data): DictationHistory
    {
        return DictationHistory::create([
            'user_id' => $user->id,
            'lesson_id' => $data['lesson_id'],
            'wpm' => $data['wpm'],
            'accuracy' => $data['accuracy'],
            'completed_at' => now(),
        ]);
    }
}

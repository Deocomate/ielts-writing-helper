<?php

namespace App\Services\Client;

use App\Jobs\GradeMockExamJob;
use App\Models\Lesson;
use App\Models\MockExam;
use App\Models\User;

class MockExamService
{
    /**
     * Get lesson for mock exam intro page.
     */
    public function getLessonForExam(int $lessonId): Lesson
    {
        return Lesson::where('status', 'published')
            ->findOrFail($lessonId);
    }

    /**
     * Submit a mock exam and get AI grading.
     */
    public function submitExam(User $user, array $data): MockExam
    {
        Lesson::where('status', 'published')->findOrFail($data['lesson_id']);

        $wordCount = str_word_count($data['user_essay']);

        $exam = MockExam::create([
            'user_id'            => $user->id,
            'lesson_id'          => $data['lesson_id'],
            'user_essay'         => $data['user_essay'],
            'word_count'         => $wordCount,
            'time_taken_seconds' => $data['time_taken_seconds'] ?? 0,
            'status'             => MockExam::STATUS_GRADING,
            'submitted_at'       => now(),
        ]);

        GradeMockExamJob::dispatch($exam->id);

        return $exam;
    }

    /**
     * Get a mock exam result by ID.
     */
    public function getExamResult(int $examId, User $user): MockExam
    {
        $exam = MockExam::where('user_id', $user->id)
            ->with('lesson')
            ->findOrFail($examId);

        if ($exam->isCompleted() && $this->isUnavailableFallbackPayload([
            'overall_band' => $exam->overall_band,
            'tr_score' => $exam->tr_score,
            'cc_score' => $exam->cc_score,
            'lr_score' => $exam->lr_score,
            'gra_score' => $exam->gra_score,
        ], is_array($exam->ai_feedback) ? $exam->ai_feedback : [])) {
            $this->failExamGrading(
                $exam,
                'Hệ thống AI chấm điểm tạm thời không khả dụng. Bài thi chưa có điểm.'
            );

            $exam->refresh();
            $exam->loadMissing('lesson');
        }

        return $exam;
    }

    /**
     * Get payload for exam status polling.
     */
    public function getExamStatusData(int $examId, User $user): array
    {
        $exam = $this->getExamResult($examId, $user);

        return [
            'exam_id' => $exam->id,
            'status' => $exam->status,
            'report_url' => route('client.learning.mock-exam.report', $exam->id),
        ];
    }

    /**
     * Save grading result after queued AI processing is completed.
     */
    public function completeExamGrading(MockExam $exam, array $gradingResult): void
    {
        $feedback = $gradingResult['feedback'] ?? [];
        if (!is_array($feedback)) {
            $feedback = [];
        }

        if ($this->isUnavailableFallbackPayload($gradingResult, $feedback)) {
            $exam->setAttribute('ai_feedback', $feedback);

            $this->failExamGrading(
                $exam,
                'Hệ thống AI chấm điểm tạm thời không khả dụng. Bài thi chưa có điểm.'
            );

            return;
        }

        $exam->forceFill([
            'overall_band' => $gradingResult['overall_band'],
            'tr_score' => $gradingResult['tr_score'],
            'cc_score' => $gradingResult['cc_score'],
            'lr_score' => $gradingResult['lr_score'],
            'gra_score' => $gradingResult['gra_score'],
            'ai_feedback' => $feedback,
            'status' => MockExam::STATUS_COMPLETED,
        ])->save();
    }

    /**
     * Mark exam grading as failed.
     */
    public function failExamGrading(MockExam $exam, string $message): void
    {
        $feedback = $exam->ai_feedback;
        if (!is_array($feedback)) {
            $feedback = [];
        }

        $feedback['overall_feedback'] = $message;
        $feedback['error'] = 'grading_failed';
        $feedback['scores_unavailable'] = true;

        $exam->forceFill([
            'overall_band' => null,
            'tr_score' => null,
            'cc_score' => null,
            'lr_score' => null,
            'gra_score' => null,
            'status' => MockExam::STATUS_FAILED,
            'ai_feedback' => $feedback,
        ])->save();
    }

    /**
     * Detect legacy fallback payload that should not be treated as a valid graded result.
     */
    private function isUnavailableFallbackPayload(array $gradingResult, array $feedback): bool
    {
        $feedbackText = mb_strtolower(implode(' ', array_filter([
            (string) ($feedback['overall_feedback'] ?? ''),
            (string) ($feedback['tr_feedback'] ?? ''),
            (string) ($feedback['cc_feedback'] ?? ''),
            (string) ($feedback['lr_feedback'] ?? ''),
            (string) ($feedback['gra_feedback'] ?? ''),
        ])), 'UTF-8');

        $usesLegacyFallbackMessage = str_contains($feedbackText, 'chấm điểm tạm thời dựa trên số từ')
            || (
                str_contains($feedbackText, 'hệ thống ai tạm thời không khả dụng')
                && str_contains($feedbackText, 'kết nối ai đang gián đoạn')
            );

        if (!$usesLegacyFallbackMessage) {
            return false;
        }

        return is_numeric($gradingResult['overall_band'] ?? null)
            && is_numeric($gradingResult['tr_score'] ?? null)
            && is_numeric($gradingResult['cc_score'] ?? null)
            && is_numeric($gradingResult['lr_score'] ?? null)
            && is_numeric($gradingResult['gra_score'] ?? null);
    }
}

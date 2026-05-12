<?php

namespace App\Jobs;

use App\Models\Lesson;
use App\Models\MockExam;
use App\Services\Client\MockExamService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class GradeMockExamJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable;

    public int $tries = 6;

    public int $timeout = 120;

    /**
     * Calculate delay between retry attempts for transient provider failures.
     *
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [30, 90, 180, 300, 600];
    }

    /**
     * Create a new job instance.
     */
    public function __construct(public readonly int $examId) {}

    /**
     * Get the middleware the job should pass through.
     *
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [
            (new WithoutOverlapping('grade-mock-exam-'.$this->examId))->releaseAfter(10)->expireAfter(180),
        ];
    }

    /**
     * Execute the job.
     */
    public function handle(MockExamService $mockExamService): void
    {
        $exam = MockExam::with('lesson')->find($this->examId);

        if (! $exam) {
            return;
        }

        if ($exam->status !== MockExam::STATUS_GRADING) {
            return;
        }

        if (! $exam->lesson) {
            $mockExamService->failExamGrading($exam, 'Không tìm thấy đề thi để chấm điểm.');

            return;
        }

        $gradingResult = $this->gradeWithAI($exam->lesson, (string) $exam->user_essay);
        $mockExamService->completeExamGrading($exam, $gradingResult);
    }

    /**
     * Handle a job failure after all retry attempts.
     */
    public function failed(Throwable $throwable): void
    {
        Log::error('Mock exam grading job exhausted retries.', [
            'exam_id' => $this->examId,
            'message' => $throwable->getMessage(),
        ]);

        $exam = MockExam::find($this->examId);
        if (! $exam || $exam->status !== MockExam::STATUS_GRADING) {
            return;
        }

        app(MockExamService::class)->failExamGrading($exam, 'Hệ thống AI chấm điểm tạm thời không khả dụng. Bài thi chưa có điểm.');
    }

    /**
     * Call OpenRouter API to grade the essay.
     *
     * @return array{overall_band: float, tr_score: float, cc_score: float, lr_score: float, gra_score: float, feedback: array<string, mixed>}
     */
    private function gradeWithAI(Lesson $lesson, string $userEssay): array
    {
        $apiKey = (string) config('services.openrouter.key', '');
        $apiUrl = (string) config('services.openrouter.url', 'https://openrouter.ai/api/v1/chat/completions');
        $model = (string) config('services.openrouter.examiner_model', config('services.openrouter.model', 'google/gemini-flash-1.5'));
        $siteUrl = (string) config('services.openrouter.site_url', config('app.url'));
        $siteName = (string) config('services.openrouter.site_name', config('app.name'));
        $chatCompletionsUrl = $this->resolveChatCompletionsUrl($apiUrl);

        if ($apiKey === '') {
            Log::warning('OpenRouter API key is not configured. Grading cannot continue.', [
                'lesson_id' => $lesson->id,
            ]);

            throw new RuntimeException('OpenRouter API key is not configured.');
        }

        $taskType = $lesson->task_type === 'task_1' ? 'Task 1' : 'Task 2';
        $minWords = $lesson->task_type === 'task_1' ? 150 : 250;
        $prompt = $this->buildGradingPrompt($taskType, $minWords, (string) $lesson->prompt_text, $userEssay);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$apiKey,
                'HTTP-Referer' => $siteUrl,
                'X-OpenRouter-Title' => $siteName,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
                ->timeout(80)
                ->post($chatCompletionsUrl, [
                    'model' => $model,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are an official IELTS Writing examiner. Apply IELTS Writing band descriptors (updated May 2023) strictly and conservatively. Never inflate scores. Penalize off-topic, non-English, memorized, copied, gibberish, or incoherent responses according to descriptor limits. Return valid JSON only.',
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt,
                        ],
                    ],
                    'stream' => false,
                ]);
        } catch (Throwable $throwable) {
            Log::error('OpenRouter grading threw an exception.', [
                'lesson_id' => $lesson->id,
                'message' => $throwable->getMessage(),
            ]);

            throw new RuntimeException('OpenRouter grading exception: '.$throwable->getMessage(), previous: $throwable);
        }

        if (! $response->successful()) {
            $statusCode = $response->status();
            $logContext = [
                'lesson_id' => $lesson->id,
                'url' => $chatCompletionsUrl,
                'model' => $model,
                'status_code' => $statusCode,
                'retry_after' => $response->header('Retry-After'),
                'body' => $response->body(),
            ];

            if ($statusCode === 429 || $statusCode >= 500) {
                Log::warning('OpenRouter grading request temporarily unavailable.', $logContext);
            } else {
                Log::error('OpenRouter grading request failed.', $logContext);
            }

            throw new RuntimeException('OpenRouter grading request failed with status '.$statusCode.'.');
        }

        $content = $response->json('choices.0.message.content');

        if (! is_string($content) || trim($content) === '') {
            Log::warning('OpenRouter response content is empty.', [
                'lesson_id' => $lesson->id,
                'raw_response' => $response->json(),
            ]);

            throw new RuntimeException('OpenRouter returned empty response content.');
        }

        $result = $this->decodeJsonPayload($content);

        if (! is_array($result) || ! $this->hasRequiredScores($result)) {
            Log::warning('OpenRouter response JSON is missing required score keys.', [
                'lesson_id' => $lesson->id,
                'raw_response' => $content,
            ]);

            throw new RuntimeException('OpenRouter returned invalid grading payload.');
        }

        $tr = $this->normalizeScore($result['tr_score'] ?? $result['ta_score'] ?? null);
        $cc = $this->normalizeScore($result['cc_score'] ?? null);
        $lr = $this->normalizeScore($result['lr_score'] ?? null);
        $gra = $this->normalizeScore($result['gra_score'] ?? null);

        [$tr, $cc, $lr, $gra, $result] = $this->applyStrictRubricCalibration(
            $lesson->task_type,
            $userEssay,
            $tr,
            $cc,
            $lr,
            $gra,
            $result
        );

        $overall = round(((float) $tr + (float) $cc + (float) $lr + (float) $gra) / 4 * 2) / 2;
        $overall = $this->normalizeScore($overall);
        $result['overall_band'] = $overall;
        $result['tr_score'] = $tr;
        $result['cc_score'] = $cc;
        $result['lr_score'] = $lr;
        $result['gra_score'] = $gra;

        return [
            'overall_band' => $overall,
            'tr_score' => $tr,
            'cc_score' => $cc,
            'lr_score' => $lr,
            'gra_score' => $gra,
            'feedback' => $result,
        ];
    }

    /**
     * Resolve API URL to OpenRouter chat completions endpoint.
     */
    private function resolveChatCompletionsUrl(string $configuredUrl): string
    {
        $normalizedUrl = rtrim(trim($configuredUrl), '/');

        if ($normalizedUrl === '') {
            return 'https://openrouter.ai/api/v1/chat/completions';
        }

        if (str_ends_with($normalizedUrl, '/chat/completions')) {
            return $normalizedUrl;
        }

        if (str_contains($normalizedUrl, '/api/v1')) {
            return $normalizedUrl.'/chat/completions';
        }

        return $normalizedUrl.'/api/v1/chat/completions';
    }

    /**
     * Decode response payload even if model wraps JSON in markdown text.
     *
     * @return array<string, mixed>|null
     */
    private function decodeJsonPayload(string $content): ?array
    {
        $decoded = json_decode($content, true);

        if (is_array($decoded)) {
            return $decoded;
        }

        $start = strpos($content, '{');
        $end = strrpos($content, '}');

        if ($start === false || $end === false || $end <= $start) {
            return null;
        }

        $candidate = substr($content, $start, ($end - $start) + 1);
        $decodedCandidate = json_decode($candidate, true);

        return is_array($decodedCandidate) ? $decodedCandidate : null;
    }

    /**
     * Build grading prompt for AI.
     */
    private function buildGradingPrompt(string $taskType, int $minWords, string $promptText, string $userEssay): string
    {
        $wordCount = str_word_count($userEssay);

        return <<<PROMPT
    Evaluate the following IELTS Writing {$taskType} essay using official IELTS Writing band descriptors (updated May 2023).

    Task facts:
    - Task type: {$taskType}
    - Recommended minimum word count: {$minWords}
    - Candidate word count: {$wordCount}

    Scoring policy (strict):
    1) Use criteria: TR, CC, LR, GRA.
    2) Score each criterion in 0.5 steps from 0.0 to 9.0.
    3) Overall band = average of 4 criteria, rounded to nearest 0.5.
    4) Do NOT inflate score. If essay is off-topic, incoherent, heavily repetitive, mostly non-English, or gibberish, score must reflect low descriptor bands.
    5) If response is 20 words or fewer, treat according to IELTS descriptor limit (Band 1 behavior).
    6) If essay barely relates to prompt or lacks a meaningful position/overview, cap TR accordingly.
    7) Feedback must cite concrete evidence from the student's text (short quoted fragments).
    8) Output language for feedback: Vietnamese.
    9) Return strict JSON only. No markdown, no explanation outside JSON.
    10) Severe underlength penalty guidance:
        - Task 2: <= 20 words should align with Band 1 behavior.
        - Task 2: < 60 words should generally not exceed low Band 3 range.
        - Task 2: < 120 words should generally not exceed Band 4 range.
        - Missing body development or paragraphing must reduce TR and CC substantially.

Question:
{$promptText}

Student Essay:
{$userEssay}

JSON shape:
{
  "overall_band": 6.5,
  "tr_score": 6.5,
  "cc_score": 7.0,
  "lr_score": 6.0,
  "gra_score": 6.5,
  "tr_feedback": "...",
  "cc_feedback": "...",
  "lr_feedback": "...",
  "gra_feedback": "...",
  "strengths": ["..."],
  "weaknesses": ["..."],
  "errors": [{"text":"...","correction":"...","type":"...","explanation":"..."}],
  "overall_feedback": "..."
}
PROMPT;
    }

    /**
     * Normalize score to IELTS 0.0 - 9.0 with 0.5 increment.
     */
    private function normalizeScore(float|int|string|null $score): float
    {
        if (! is_numeric($score)) {
            return 0.0;
        }

        $value = (float) $score;
        $rounded = round($value * 2) / 2;

        return min(9.0, max(0.0, $rounded));
    }

    /**
     * Ensure AI payload includes all criteria scores.
     */
    private function hasRequiredScores(array $result): bool
    {
        return is_numeric($result['tr_score'] ?? $result['ta_score'] ?? null)
            && is_numeric($result['cc_score'] ?? null)
            && is_numeric($result['lr_score'] ?? null)
            && is_numeric($result['gra_score'] ?? null);
    }

    /**
     * Apply strict IELTS guard rails so short/underdeveloped responses are not over-scored.
     *
     * @param  array<string, mixed>  $feedback
     * @return array{0: float, 1: float, 2: float, 3: float, 4: array<string, mixed>}
     */
    private function applyStrictRubricCalibration(
        string $taskType,
        string $essay,
        float $tr,
        float $cc,
        float $lr,
        float $gra,
        array $feedback
    ): array {
        $wordCount = str_word_count($essay);
        $paragraphCount = $this->countParagraphs($essay);
        $sentenceCount = $this->countSentences($essay);
        $notes = [];

        if ($taskType === 'task_2') {
            if ($wordCount <= 20) {
                $tr = min($tr, 1.0);
                $cc = min($cc, 1.0);
                $lr = min($lr, 1.0);
                $gra = min($gra, 1.0);
                $notes[] = 'Bai viet <= 20 tu phai ap dung khung diem rat thap theo mo ta IELTS.';
            } elseif ($wordCount < 60) {
                $tr = min($tr, 2.5);
                $cc = min($cc, 2.5);
                $lr = min($lr, 3.0);
                $gra = min($gra, 3.0);
                $notes[] = 'Bai viet qua ngan (< 60 tu) nen khong du co so de dat diem cao.';
            } elseif ($wordCount < 120) {
                $tr = min($tr, 3.5);
                $cc = min($cc, 3.5);
                $lr = min($lr, 4.0);
                $gra = min($gra, 4.0);
                $notes[] = 'Do dai bai viet thieu nghiem trong (< 120 tu), gioi han diem theo tieu chi IELTS.';
            } elseif ($wordCount < 180) {
                $tr = min($tr, 4.5);
                $cc = min($cc, 4.5);
                $notes[] = 'Bai viet duoi nguong phat trien y day du cho Task 2, TR/CC bi gioi han.';
            }

            if ($paragraphCount < 2) {
                $cc = min($cc, 3.0);
                $notes[] = 'Thieu cau truc doan van (paragraphing), han che diem CC.';
            }

            if ($sentenceCount < 3) {
                $tr = min($tr, 3.0);
                $cc = min($cc, 3.0);
                $notes[] = 'So cau qua it de the hien phat trien lap luan cho Task 2.';
            }
        } else {
            if ($wordCount <= 20) {
                $tr = min($tr, 1.0);
                $cc = min($cc, 1.0);
                $lr = min($lr, 1.0);
                $gra = min($gra, 1.0);
                $notes[] = 'Bai viet <= 20 tu phai ap dung khung diem rat thap theo mo ta IELTS.';
            } elseif ($wordCount < 50) {
                $tr = min($tr, 2.5);
                $cc = min($cc, 2.5);
                $lr = min($lr, 3.0);
                $gra = min($gra, 3.0);
                $notes[] = 'Bai viet qua ngan cho Task 1, khong du bao quat yeu cau de bai.';
            } elseif ($wordCount < 100) {
                $tr = min($tr, 3.5);
                $cc = min($cc, 3.5);
                $lr = min($lr, 4.0);
                $gra = min($gra, 4.0);
                $notes[] = 'Do dai bai viet chua dat muc toi thieu de danh gia day du Task 1.';
            }
        }

        $tr = $this->normalizeScore($tr);
        $cc = $this->normalizeScore($cc);
        $lr = $this->normalizeScore($lr);
        $gra = $this->normalizeScore($gra);

        if (! empty($notes)) {
            $calibrationNote = implode(' ', array_unique($notes));
            $feedback['calibration_note'] = $calibrationNote;
            $feedback['tr_feedback'] = $this->appendFeedbackNote($feedback['tr_feedback'] ?? null, $calibrationNote);
            $feedback['cc_feedback'] = $this->appendFeedbackNote($feedback['cc_feedback'] ?? null, $calibrationNote);

            if (empty($feedback['overall_feedback'])) {
                $feedback['overall_feedback'] = $calibrationNote;
            } else {
                $feedback['overall_feedback'] = $this->appendFeedbackNote($feedback['overall_feedback'], $calibrationNote);
            }
        }

        return [$tr, $cc, $lr, $gra, $feedback];
    }

    private function appendFeedbackNote(mixed $existing, string $note): string
    {
        $text = trim((string) $existing);

        if ($text === '') {
            return $note;
        }

        if (str_contains(mb_strtolower($text, 'UTF-8'), mb_strtolower($note, 'UTF-8'))) {
            return $text;
        }

        return $text.' '.$note;
    }

    private function countParagraphs(string $essay): int
    {
        $paragraphs = preg_split('/\R\s*\R/u', trim($essay));
        if (! is_array($paragraphs)) {
            return 0;
        }

        $filtered = array_filter($paragraphs, static fn (string $paragraph): bool => trim($paragraph) !== '');

        return count($filtered);
    }

    private function countSentences(string $essay): int
    {
        $sentenceCount = preg_match_all('/[.!?]+(?:\s|$)/u', $essay);

        if (! is_int($sentenceCount) || $sentenceCount < 1) {
            $sentenceCount = preg_match_all('/\n+/u', trim($essay));
        }

        return max(1, (int) $sentenceCount);
    }
}

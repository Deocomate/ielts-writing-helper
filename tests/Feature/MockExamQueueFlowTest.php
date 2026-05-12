<?php

use App\Jobs\GradeMockExamJob;
use App\Models\Lesson;
use App\Models\MockExam;
use App\Models\User;
use App\Services\Client\MockExamService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use RuntimeException;

uses(RefreshDatabase::class);

function createMockExamLesson(): Lesson
{
    return Lesson::query()->create([
        'title' => 'Task 2 - Education Reform',
        'task_type' => 'task_2',
        'question_type' => 'opinion',
        'prompt_text' => 'Some people think schools should focus more on life skills than academic subjects. Discuss both views and give your opinion.',
        'sample_essay' => 'Sample essay content for testing queued grading.',
        'is_premium' => true,
        'status' => 'published',
    ]);
}

test('submit mock exam queues grading job and stores grading status', function () {
    Queue::fake();

    $user = User::factory()->create([
        'role' => 'user',
        'subscription_tier' => 'pro',
        'subscription_expires_at' => now()->addDays(30),
        'status' => 'active',
    ]);

    $lesson = createMockExamLesson();
    $essay = trim(str_repeat('This essay sentence improves test reliability. ', 40));

    $response = $this->actingAs($user)->post(route('client.learning.mock-exam.submit'), [
        'lesson_id' => $lesson->id,
        'user_essay' => $essay,
        'time_taken_seconds' => 1800,
    ]);

    $exam = MockExam::query()->latest('id')->first();

    expect($exam)->not->toBeNull()
        ->and($exam->status)->toBe(MockExam::STATUS_GRADING)
        ->and($exam->overall_band)->toBeNull()
        ->and($exam->word_count)->toBeGreaterThan(0);

    $response->assertRedirect(route('client.learning.mock-exam.report', $exam->id));

    Queue::assertPushed(GradeMockExamJob::class, function (GradeMockExamJob $job) use ($exam) {
        return $job->examId === $exam->id;
    });
});

test('mock exam status endpoint returns grading payload for polling', function () {
    $user = User::factory()->create([
        'role' => 'user',
        'subscription_tier' => 'pro',
        'subscription_expires_at' => now()->addDays(15),
        'status' => 'active',
    ]);

    $lesson = createMockExamLesson();

    $exam = MockExam::query()->create([
        'user_id' => $user->id,
        'lesson_id' => $lesson->id,
        'user_essay' => trim(str_repeat('Queued grading test essay. ', 30)),
        'word_count' => 150,
        'time_taken_seconds' => 900,
        'status' => MockExam::STATUS_GRADING,
        'submitted_at' => now(),
    ]);

    $response = $this->actingAs($user)->getJson(route('client.learning.mock-exam.status', $exam->id));

    $response->assertOk()->assertJson([
        'exam_id' => $exam->id,
        'status' => MockExam::STATUS_GRADING,
        'report_url' => route('client.learning.mock-exam.report', $exam->id),
    ]);
});

test('grading report page shows loading message while ai processing is in queue', function () {
    $user = User::factory()->create([
        'role' => 'user',
        'subscription_tier' => 'pro',
        'subscription_expires_at' => now()->addDays(10),
        'status' => 'active',
    ]);

    $lesson = createMockExamLesson();

    $exam = MockExam::query()->create([
        'user_id' => $user->id,
        'lesson_id' => $lesson->id,
        'user_essay' => trim(str_repeat('Loading state essay. ', 20)),
        'word_count' => 120,
        'time_taken_seconds' => 600,
        'status' => MockExam::STATUS_GRADING,
        'submitted_at' => now(),
    ]);

    $response = $this->actingAs($user)->get(route('client.learning.mock-exam.report', $exam->id));

    $response->assertOk()->assertSeeText('AI đang phân tích bài luận của bạn (TR, CC, LR, GRA)...');
});

test('submit mock exam rejects oversized essay payload', function () {
    Queue::fake();

    $user = User::factory()->create([
        'role' => 'user',
        'subscription_tier' => 'pro',
        'subscription_expires_at' => now()->addDays(20),
        'status' => 'active',
    ]);

    $lesson = createMockExamLesson();
    $oversizedEssay = str_repeat('x', 12001);

    $response = $this->actingAs($user)->post(route('client.learning.mock-exam.submit'), [
        'lesson_id' => $lesson->id,
        'user_essay' => $oversizedEssay,
        'time_taken_seconds' => 1200,
    ]);

    $response->assertSessionHasErrors('user_essay');
    Queue::assertNothingPushed();
});

test('grading job uses staggered retry backoff for transient ai failures', function () {
    $job = new GradeMockExamJob(999);

    expect($job->tries)->toBe(6)
        ->and($job->backoff())->toBe([30, 90, 180, 300, 600]);
});

test('failed grading marks exam as unavailable with no score', function () {
    $user = User::factory()->create([
        'role' => 'user',
        'subscription_tier' => 'pro',
        'subscription_expires_at' => now()->addDays(10),
        'status' => 'active',
    ]);

    $lesson = createMockExamLesson();

    $exam = MockExam::query()->create([
        'user_id' => $user->id,
        'lesson_id' => $lesson->id,
        'user_essay' => trim(str_repeat('AI unavailable test essay. ', 20)),
        'word_count' => 120,
        'time_taken_seconds' => 600,
        'status' => MockExam::STATUS_GRADING,
        'overall_band' => 5.0,
        'tr_score' => 5.0,
        'cc_score' => 5.0,
        'lr_score' => 5.0,
        'gra_score' => 5.0,
        'submitted_at' => now(),
    ]);

    (new GradeMockExamJob($exam->id))->failed(new RuntimeException('OpenRouter unavailable.'));

    $exam->refresh();

    expect($exam->status)->toBe(MockExam::STATUS_FAILED)
        ->and($exam->overall_band)->toBeNull()
        ->and($exam->tr_score)->toBeNull()
        ->and($exam->cc_score)->toBeNull()
        ->and($exam->lr_score)->toBeNull()
        ->and($exam->gra_score)->toBeNull();
});

test('failed grading report shows ai unavailable screen instead of score cards', function () {
    $user = User::factory()->create([
        'role' => 'user',
        'subscription_tier' => 'pro',
        'subscription_expires_at' => now()->addDays(10),
        'status' => 'active',
    ]);

    $lesson = createMockExamLesson();

    $exam = MockExam::query()->create([
        'user_id' => $user->id,
        'lesson_id' => $lesson->id,
        'user_essay' => trim(str_repeat('Failed report test essay. ', 20)),
        'word_count' => 120,
        'time_taken_seconds' => 600,
        'status' => MockExam::STATUS_FAILED,
        'ai_feedback' => [
            'overall_feedback' => 'Hệ thống AI chấm điểm tạm thời không khả dụng. Bài thi chưa có điểm.',
            'scores_unavailable' => true,
        ],
        'submitted_at' => now(),
    ]);

    $response = $this->actingAs($user)->get(route('client.learning.mock-exam.report', $exam->id));

    $response->assertOk()
        ->assertSeeText('Hệ thống AI tạm thời không khả dụng')
        ->assertSeeText('Bài thi hiện chưa có điểm.')
        ->assertDontSeeText('Kết quả có thể chênh lệch ±0.5 so với thi thật.');
});

test('legacy fallback payload is downgraded to failed no-score state', function () {
    $user = User::factory()->create([
        'role' => 'user',
        'subscription_tier' => 'pro',
        'subscription_expires_at' => now()->addDays(10),
        'status' => 'active',
    ]);

    $lesson = createMockExamLesson();

    $exam = MockExam::query()->create([
        'user_id' => $user->id,
        'lesson_id' => $lesson->id,
        'user_essay' => trim(str_repeat('Legacy fallback detection test essay. ', 20)),
        'word_count' => 120,
        'time_taken_seconds' => 600,
        'status' => MockExam::STATUS_GRADING,
        'submitted_at' => now(),
    ]);

    app(MockExamService::class)->completeExamGrading($exam, [
        'overall_band' => 5.0,
        'tr_score' => 5.0,
        'cc_score' => 5.0,
        'lr_score' => 5.0,
        'gra_score' => 5.0,
        'feedback' => [
            'overall_band' => 5.0,
            'tr_score' => 5.0,
            'cc_score' => 5.0,
            'lr_score' => 5.0,
            'gra_score' => 5.0,
            'tr_feedback' => 'Hệ thống AI tạm thời không khả dụng. Vui lòng thử lại sau.',
            'cc_feedback' => 'Hệ thống AI tạm thời không khả dụng.',
            'lr_feedback' => 'Hệ thống AI tạm thời không khả dụng.',
            'gra_feedback' => 'Hệ thống AI tạm thời không khả dụng.',
            'overall_feedback' => 'Chấm điểm tạm thời dựa trên số từ vì kết nối AI đang gián đoạn.',
        ],
    ]);

    $exam->refresh();

    expect($exam->status)->toBe(MockExam::STATUS_FAILED)
        ->and($exam->overall_band)->toBeNull()
        ->and($exam->tr_score)->toBeNull()
        ->and($exam->cc_score)->toBeNull()
        ->and($exam->lr_score)->toBeNull()
        ->and($exam->gra_score)->toBeNull()
        ->and(data_get($exam->ai_feedback, 'scores_unavailable'))->toBeTrue();
});

test('loading legacy completed fallback result auto-corrects to failed state', function () {
    $user = User::factory()->create([
        'role' => 'user',
        'subscription_tier' => 'pro',
        'subscription_expires_at' => now()->addDays(10),
        'status' => 'active',
    ]);

    $lesson = createMockExamLesson();

    $exam = MockExam::query()->create([
        'user_id' => $user->id,
        'lesson_id' => $lesson->id,
        'user_essay' => trim(str_repeat('Auto-heal legacy result test essay. ', 20)),
        'word_count' => 120,
        'time_taken_seconds' => 600,
        'status' => MockExam::STATUS_COMPLETED,
        'overall_band' => 5.0,
        'tr_score' => 5.0,
        'cc_score' => 5.0,
        'lr_score' => 5.0,
        'gra_score' => 5.0,
        'ai_feedback' => [
            'overall_band' => 5.0,
            'tr_score' => 5.0,
            'cc_score' => 5.0,
            'lr_score' => 5.0,
            'gra_score' => 5.0,
            'tr_feedback' => 'Hệ thống AI tạm thời không khả dụng. Vui lòng thử lại sau.',
            'cc_feedback' => 'Hệ thống AI tạm thời không khả dụng.',
            'lr_feedback' => 'Hệ thống AI tạm thời không khả dụng.',
            'gra_feedback' => 'Hệ thống AI tạm thời không khả dụng.',
            'overall_feedback' => 'Chấm điểm tạm thời dựa trên số từ vì kết nối AI đang gián đoạn.',
        ],
        'submitted_at' => now(),
    ]);

    $result = app(MockExamService::class)->getExamResult($exam->id, $user);

    expect($result->status)->toBe(MockExam::STATUS_FAILED)
        ->and($result->overall_band)->toBeNull()
        ->and($result->tr_score)->toBeNull()
        ->and($result->cc_score)->toBeNull()
        ->and($result->lr_score)->toBeNull()
        ->and($result->gra_score)->toBeNull()
        ->and(data_get($result->ai_feedback, 'scores_unavailable'))->toBeTrue();
});

test('short task2 essay is hard capped even when ai returns optimistic score', function () {
    $user = User::factory()->create([
        'role' => 'user',
        'subscription_tier' => 'pro',
        'subscription_expires_at' => now()->addDays(10),
        'status' => 'active',
    ]);

    $lesson = createMockExamLesson();
    $essay = 'The question of whether university education should be free for all students is widely debated. While free higher education offers undeniable benefits, I believe that making it completely free for everyone is neither practical nor beneficial in the long run.';

    $exam = MockExam::query()->create([
        'user_id' => $user->id,
        'lesson_id' => $lesson->id,
        'user_essay' => $essay,
        'word_count' => str_word_count($essay),
        'time_taken_seconds' => 55,
        'status' => MockExam::STATUS_GRADING,
        'submitted_at' => now(),
    ]);

    Http::fake([
        '*' => Http::response([
            'choices' => [[
                'message' => [
                    'content' => json_encode([
                        'overall_band' => 6.5,
                        'tr_score' => 6.0,
                        'cc_score' => 6.5,
                        'lr_score' => 6.5,
                        'gra_score' => 6.0,
                        'tr_feedback' => 'AI optimistic score before calibration.',
                        'cc_feedback' => 'AI optimistic score before calibration.',
                        'lr_feedback' => 'AI optimistic score before calibration.',
                        'gra_feedback' => 'AI optimistic score before calibration.',
                        'overall_feedback' => 'AI optimistic score before calibration.',
                    ], JSON_UNESCAPED_UNICODE),
                ],
            ]],
        ], 200),
    ]);

    (new GradeMockExamJob($exam->id))->handle(app(MockExamService::class));
    $exam->refresh();

    expect($exam->status)->toBe(MockExam::STATUS_COMPLETED)
        ->and((float) $exam->tr_score)->toBeLessThanOrEqual(2.5)
        ->and((float) $exam->cc_score)->toBeLessThanOrEqual(2.5)
        ->and((float) $exam->lr_score)->toBeLessThanOrEqual(3.0)
        ->and((float) $exam->gra_score)->toBeLessThanOrEqual(3.0)
        ->and((float) $exam->overall_band)->toBe(3.0)
        ->and(data_get($exam->ai_feedback, 'calibration_note'))->not->toBeNull();
});

test('task2 response at 20 words or fewer is forced to band 1 range', function () {
    $user = User::factory()->create([
        'role' => 'user',
        'subscription_tier' => 'pro',
        'subscription_expires_at' => now()->addDays(10),
        'status' => 'active',
    ]);

    $lesson = createMockExamLesson();
    $essay = 'University should be free for everyone because education is important and life changing for poor students worldwide.';

    $exam = MockExam::query()->create([
        'user_id' => $user->id,
        'lesson_id' => $lesson->id,
        'user_essay' => $essay,
        'word_count' => str_word_count($essay),
        'time_taken_seconds' => 25,
        'status' => MockExam::STATUS_GRADING,
        'submitted_at' => now(),
    ]);

    Http::fake([
        '*' => Http::response([
            'choices' => [[
                'message' => [
                    'content' => json_encode([
                        'overall_band' => 5.5,
                        'tr_score' => 5.5,
                        'cc_score' => 5.5,
                        'lr_score' => 5.5,
                        'gra_score' => 5.5,
                        'tr_feedback' => 'AI optimistic score before calibration.',
                        'cc_feedback' => 'AI optimistic score before calibration.',
                        'lr_feedback' => 'AI optimistic score before calibration.',
                        'gra_feedback' => 'AI optimistic score before calibration.',
                        'overall_feedback' => 'AI optimistic score before calibration.',
                    ], JSON_UNESCAPED_UNICODE),
                ],
            ]],
        ], 200),
    ]);

    (new GradeMockExamJob($exam->id))->handle(app(MockExamService::class));
    $exam->refresh();

    expect($exam->status)->toBe(MockExam::STATUS_COMPLETED)
        ->and((float) $exam->tr_score)->toBe(1.0)
        ->and((float) $exam->cc_score)->toBe(1.0)
        ->and((float) $exam->lr_score)->toBe(1.0)
        ->and((float) $exam->gra_score)->toBe(1.0)
        ->and((float) $exam->overall_band)->toBe(1.0);
});

<?php

use App\Models\Lesson;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createVocabularyLesson(): Lesson
{
    return Lesson::query()->create([
        'title' => 'Task 1 - Vocabulary Save Flow',
        'task_type' => 'task_1',
        'question_type' => 'line_graph',
        'prompt_text' => 'The chart illustrates energy usage.',
        'sample_essay' => 'Electricity increased steadily over the period.',
        'band_score' => 7.5,
        'tr_score' => 7.5,
        'cc_score' => 7.0,
        'lr_score' => 7.5,
        'gra_score' => 7.0,
        'is_premium' => false,
        'status' => 'published',
    ]);
}

test('the application returns a successful response', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});

test('save vocabulary endpoint returns created status for first json request', function () {
    $user = User::factory()->create([
        'role' => 'user',
        'status' => 'active',
        'subscription_tier' => 'free',
        'subscription_expires_at' => null,
    ]);

    $lesson = createVocabularyLesson();

    $response = $this->actingAs($user)->postJson(route('client.vocabulary.store'), [
        'word' => 'steadily',
        'meaning' => 'mot cach on dinh',
        'lesson_id' => $lesson->id,
        'context_sentence' => 'The figure rose steadily over the period.',
    ]);

    $response->assertCreated()->assertJson([
        'status' => 'created',
        'vocabulary' => [
            'word' => 'steadily',
            'meaning' => 'mot cach on dinh',
        ],
    ]);

    $this->assertDatabaseHas('user_vocabularies', [
        'user_id' => $user->id,
        'word' => 'steadily',
        'meaning' => 'mot cach on dinh',
    ]);
});

test('save vocabulary endpoint returns already_exists without creating duplicates', function () {
    $user = User::factory()->create([
        'role' => 'user',
        'status' => 'active',
        'subscription_tier' => 'free',
        'subscription_expires_at' => null,
    ]);

    $lesson = createVocabularyLesson();

    $this->actingAs($user)->postJson(route('client.vocabulary.store'), [
        'word' => 'steadily',
        'meaning' => 'mot cach on dinh',
        'lesson_id' => $lesson->id,
    ])->assertCreated();

    $response = $this->actingAs($user)->postJson(route('client.vocabulary.store'), [
        'word' => 'steadily',
        'meaning' => 'at a regular and even pace',
        'lesson_id' => $lesson->id,
    ]);

    $response->assertOk()->assertJson([
        'status' => 'already_exists',
    ]);

    $this->assertDatabaseCount('user_vocabularies', 1);
});

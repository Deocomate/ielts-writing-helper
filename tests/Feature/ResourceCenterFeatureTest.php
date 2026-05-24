<?php

use App\Models\MiniExercise;
use App\Models\ReadingMaterial;
use App\Models\Setting;
use App\Models\User;
use App\Services\SettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('setting service normalizes common youtube url formats', function (string $url) {
    expect(app(SettingService::class)->normalizeYoutubeEmbedUrl($url))
        ->toBe('https://www.youtube.com/embed/dQw4w9WgXcQ');
})->with([
    'watch' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
    'watch with query' => 'https://www.youtube.com/watch?si=abc&v=dQw4w9WgXcQ&t=10',
    'short' => 'https://youtube.com/shorts/dQw4w9WgXcQ?feature=share',
    'embed' => 'https://www.youtube.com/embed/dQw4w9WgXcQ',
    'short domain' => 'https://youtu.be/dQw4w9WgXcQ?si=abc',
]);

test('admin can update demo video setting and invalid youtube url is rejected', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'status' => 'active',
    ]);

    $this->actingAs($admin)->put(route('admin.settings.general.update'), [
        'demo_video_url' => 'https://youtu.be/dQw4w9WgXcQ?si=abc',
    ])->assertRedirect();

    expect(Setting::query()->where('key', SettingService::DEMO_VIDEO_URL_KEY)->value('value'))
        ->toBe('https://www.youtube.com/embed/dQw4w9WgXcQ');

    $this->actingAs($admin)->from(route('admin.settings.general.edit'))->put(route('admin.settings.general.update'), [
        'demo_video_url' => 'https://example.com/video',
    ])->assertRedirect(route('admin.settings.general.edit'))
        ->assertSessionHasErrors('demo_video_url');
});

test('home page renders demo modal trigger when video setting exists', function () {
    Setting::query()->create([
        'key' => SettingService::DEMO_VIDEO_URL_KEY,
        'value' => 'https://www.youtube.com/embed/dQw4w9WgXcQ',
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('open-demo-video')
        ->assertSee('https://www.youtube.com/embed/dQw4w9WgXcQ');
});

test('admin can create reading material with slug and vocabulary notes', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'status' => 'active',
    ]);

    $this->actingAs($admin)->post(route('admin.reading-materials.store'), [
        'title' => 'Technology and Human Creativity',
        'topic' => 'technology',
        'excerpt' => 'A reading material for IELTS learners.',
        'content' => '<p>Technology can enhance creativity.</p>',
        'vocabulary_notes' => [
            ['term' => 'creativity', 'meaning' => 'sự sáng tạo', 'note' => 'Useful noun.'],
        ],
        'status' => 'published',
    ])->assertRedirect(route('admin.reading-materials.index'));

    $material = ReadingMaterial::query()->firstOrFail();

    expect($material->slug)->toBe('technology-and-human-creativity')
        ->and($material->vocabulary_notes[0]['term'])->toBe('creativity');

    $this->get(route('client.reading-materials.show', $material))
        ->assertOk()
        ->assertSee('Technology and Human Creativity');
});

test('reading material and mini exercise resolve routes by slug', function () {
    $user = User::factory()->create([
        'role' => 'user',
        'status' => 'active',
    ]);
    $material = ReadingMaterial::factory()->create([
        'title' => 'Environment Speech',
        'slug' => 'environment-speech',
        'status' => 'published',
        'views_count' => 0,
    ]);
    $exercise = MiniExercise::factory()->create([
        'title' => 'Past tense correction',
        'slug' => 'past-tense-correction',
        'status' => 'published',
    ]);

    $this->actingAs($user)->get(route('client.reading-materials.show', $material))
        ->assertOk()
        ->assertSee('Environment Speech');

    expect($material->fresh()->views_count)->toBe(1);

    $this->actingAs($user)->get(route('client.mini-exercises.show', $exercise))
        ->assertOk()
        ->assertSee('Past tense correction');
});

test('library resource tabs show published records and keep tab query in pagination', function () {
    $user = User::factory()->create([
        'role' => 'user',
        'status' => 'active',
    ]);

    ReadingMaterial::factory()->count(13)->create(['status' => 'published']);
    ReadingMaterial::factory()->create(['title' => 'Hidden Draft Material', 'status' => 'draft']);
    MiniExercise::factory()->create(['title' => 'Published Exercise', 'status' => 'published']);
    MiniExercise::factory()->create(['title' => 'Hidden Draft Exercise', 'status' => 'draft']);

    $materialsResponse = $this->actingAs($user)->get(route('client.lessons.library', ['tab' => 'materials']));
    $materialsResponse->assertOk()
        ->assertDontSee('Hidden Draft Material')
        ->assertSee('tab=materials', false)
        ->assertSee('page=2', false);

    $this->actingAs($user)->get(route('client.lessons.library', ['tab' => 'exercises']))
        ->assertOk()
        ->assertSee('Published Exercise')
        ->assertDontSee('Hidden Draft Exercise');
});

test('admin can create all mini exercise types with normalized payloads', function (array $payload, string $expectedType) {
    $admin = User::factory()->create([
        'role' => 'admin',
        'status' => 'active',
    ]);

    $this->actingAs($admin)->post(route('admin.mini-exercises.store'), array_merge([
        'title' => 'Exercise '.$expectedType,
        'mistake_type' => 'tense',
        'difficulty_level' => 'easy',
        'explanation' => 'This is the explanation.',
        'status' => 'published',
    ], $payload))->assertRedirect(route('admin.mini-exercises.index'));

    $exercise = MiniExercise::query()->where('exercise_type', $expectedType)->firstOrFail();

    expect($exercise->slug)->toStartWith('exercise-')
        ->and($exercise->question_data)->toBeArray();
})->with([
    'fill blank' => [[
        'exercise_type' => 'fill_blank',
        'marked_sentence' => 'She [went] to school yesterday.',
        'distractors' => 'go, gone',
    ], 'fill_blank'],
    'drag drop' => [[
        'exercise_type' => 'drag_drop',
        'marked_sentence' => 'She [went] to [school] yesterday.',
        'distractors' => 'go, park',
    ], 'drag_drop'],
    'short answer' => [[
        'exercise_type' => 'short_answer',
        'prompt' => 'Correct this verb: She go yesterday.',
        'accepted_answers' => 'went, She went yesterday.',
    ], 'short_answer'],
]);

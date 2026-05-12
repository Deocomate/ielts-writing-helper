<?php

namespace Database\Seeders;

use App\Models\Lesson;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LearningHistorySeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $clients = User::query()->where('role', 'user')->orderBy('id')->get();
        $lessons = Lesson::query()->orderBy('id')->get();

        DB::table('dictation_histories')->truncate();
        DB::table('mock_exams')->truncate();
        DB::table('user_vocabularies')->truncate();

        foreach ($clients as $index => $client) {
            foreach ($lessons->take(3) as $lessonIndex => $lesson) {
                DB::table('dictation_histories')->insert([
                    'user_id' => $client->id,
                    'lesson_id' => $lesson->id,
                    'wpm' => 32 + (($index + $lessonIndex) % 45),
                    'accuracy' => 78 + (($index + $lessonIndex) % 20),
                    'completed_at' => now()->subDays(rand(1, 40)),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if ($client->subscription_tier === 'pro') {
                foreach ($lessons->where('task_type', 'task_2')->take(2) as $lesson) {
                    DB::table('mock_exams')->insert([
                        'user_id' => $client->id,
                        'lesson_id' => $lesson->id,
                        'user_essay' => 'This is a seeded mock exam essay used for end-to-end testing across admin and learner modules.',
                        'word_count' => rand(260, 340),
                        'time_taken_seconds' => rand(1800, 2800),
                        'status' => 'completed',
                        'overall_band' => number_format(rand(60, 85) / 10, 1),
                        'tr_score' => number_format(rand(60, 85) / 10, 1),
                        'cc_score' => number_format(rand(60, 85) / 10, 1),
                        'lr_score' => number_format(rand(60, 85) / 10, 1),
                        'gra_score' => number_format(rand(60, 85) / 10, 1),
                        'ai_feedback' => json_encode([
                            'overall_comment' => 'Logical structure is clear. Improve lexical precision in body paragraph 2.',
                            'strengths' => ['cohesion', 'task response'],
                            'improvements' => ['grammar range', 'word choice'],
                        ], JSON_UNESCAPED_UNICODE),
                        'submitted_at' => now()->subDays(rand(1, 30)),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::table('user_vocabularies')->insert([
                'user_id' => $client->id,
                'lesson_id' => $lessons->first()?->id,
                'word' => 'cohesion-' . $client->id,
                'meaning' => 'Sự liên kết mạch lạc trong bài viết',
                'context_sentence' => 'The essay demonstrates strong cohesion between ideas.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Lesson;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LessonSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $lessons = [
            [
                'title' => 'Task 1 - Line Graph Energy Consumption',
                'task_type' => 'task_1',
                'question_type' => 'line_graph',
                'prompt_text' => 'The chart below shows household energy consumption over a 20-year period.',
                'sample_essay' => 'The line graph illustrates household energy usage over two decades. Overall, electricity increased steadily while coal declined significantly.',
                'band_score' => 8.0,
                'tr_score' => 8.0,
                'cc_score' => 8.0,
                'lr_score' => 8.0,
                'gra_score' => 7.5,
                'is_premium' => false,
                'status' => 'published',
            ],
            [
                'title' => 'Task 1 - Map Changes in a Town',
                'task_type' => 'task_1',
                'question_type' => 'map',
                'prompt_text' => 'The maps below show the development of a town center between 1990 and 2020.',
                'sample_essay' => 'The maps compare the town centre in 1990 and 2020. It is clear that the area became more residential and transport infrastructure was expanded.',
                'band_score' => 7.5,
                'tr_score' => 7.5,
                'cc_score' => 7.5,
                'lr_score' => 7.0,
                'gra_score' => 7.5,
                'is_premium' => false,
                'status' => 'published',
            ],
            [
                'title' => 'Task 2 - University Education Should Be Free',
                'task_type' => 'task_2',
                'question_type' => 'agree_disagree',
                'prompt_text' => 'Some people believe university education should be free for everyone. Discuss your opinion.',
                'sample_essay' => 'I largely agree that tertiary education should be publicly funded, although a partial tuition model can still be justified in certain contexts.',
                'band_score' => 8.5,
                'tr_score' => 8.5,
                'cc_score' => 8.0,
                'lr_score' => 8.5,
                'gra_score' => 8.0,
                'is_premium' => true,
                'status' => 'published',
            ],
            [
                'title' => 'Task 2 - Remote Work Benefits and Drawbacks',
                'task_type' => 'task_2',
                'question_type' => 'advantages_disadvantages',
                'prompt_text' => 'What are the advantages and disadvantages of remote working?',
                'sample_essay' => 'Remote work improves flexibility and can reduce costs, yet it may weaken collaboration and blur work-life boundaries.',
                'band_score' => 7.0,
                'tr_score' => 7.0,
                'cc_score' => 7.0,
                'lr_score' => 7.0,
                'gra_score' => 7.0,
                'is_premium' => true,
                'status' => 'draft',
            ],
        ];

        foreach ($lessons as $lesson) {
            $record = Lesson::updateOrCreate(['title' => $lesson['title']], $lesson);

            $record->annotations()->delete();
            $record->vocabularies()->delete();

            $record->annotations()->createMany([
                [
                    'selected_text' => 'Overall, electricity increased steadily',
                    'start_offset' => 0,
                    'end_offset' => 38,
                    'tag_type' => 'coherence',
                    'access_tier' => 'free',
                    'explanation' => 'Câu overview rõ ràng, nêu xu hướng chính ngay đầu bài.',
                ],
                [
                    'selected_text' => 'declined significantly',
                    'start_offset' => 60,
                    'end_offset' => 82,
                    'tag_type' => 'vocabulary',
                    'access_tier' => 'pro',
                    'explanation' => 'Dùng cụm học thuật thay cho từ đơn giản như "went down a lot".',
                ],
            ]);

            $record->vocabularies()->createMany([
                [
                    'word' => 'steadily',
                    'meaning_vi' => 'một cách ổn định',
                    'meaning_en' => 'at a regular and even pace',
                    'example_sentence' => 'The figure rose steadily over the period.',
                    'access_tier' => 'free',
                ],
                [
                    'word' => 'significantly',
                    'meaning_vi' => 'đáng kể',
                    'meaning_en' => 'in a sufficiently great amount',
                    'example_sentence' => 'Coal consumption decreased significantly.',
                    'access_tier' => 'pro',
                ],
            ]);
        }
    }
}

<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\Client\LessonLibraryService;
use App\Services\MiniExerciseService;
use App\Services\ReadingMaterialService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LessonController extends Controller
{
    public function __construct(
        private readonly LessonLibraryService $lessonService,
        private readonly ReadingMaterialService $readingMaterialService,
        private readonly MiniExerciseService $miniExerciseService
    ) {}

    public function library(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'task_type' => ['nullable', 'in:task_1,task_2,Task 1,Task 2'],
            'question_type' => [
                'nullable',
                'in:line_graph,bar_chart,pie_chart,map,table,mixed_chart,process_diagram,flowchart,opinion,discussion,problem_solution,advantages_disadvantages,two_part,causes_effects,positive_negative',
            ],
            'access' => ['nullable', 'in:free,pro,premium'],
            'sort' => ['nullable', 'in:latest,band_high,band_low,band_desc,band_asc'],
            'band_min' => ['nullable', 'numeric', 'between:0,9'],
            'tab' => ['nullable', 'in:ielts,materials,exercises'],
            'topic' => ['nullable', 'in:people,environment,technology,inspiration'],
            'mistake_type' => ['nullable', 'in:tense,structure,vocabulary,punctuation'],
            'exercise_type' => ['nullable', 'in:fill_blank,drag_drop,short_answer'],
        ]);

        if (($filters['task_type'] ?? null) === 'Task 1') {
            $filters['task_type'] = 'task_1';
        }
        if (($filters['task_type'] ?? null) === 'Task 2') {
            $filters['task_type'] = 'task_2';
        }
        if (($filters['access'] ?? null) === 'premium') {
            $filters['access'] = 'pro';
        }
        if (($filters['sort'] ?? null) === 'band_desc') {
            $filters['sort'] = 'band_high';
        }
        if (($filters['sort'] ?? null) === 'band_asc') {
            $filters['sort'] = 'band_low';
        }

        return view('client.learning.lesson-library', [
            'lessons' => $this->lessonService->getLessons($filters),
            'totalCount' => $this->lessonService->getTotalCount(),
            'materials' => $this->readingMaterialService->getMaterials([
                'published_only' => true,
                'search' => $filters['search'] ?? null,
                'topic' => $filters['topic'] ?? null,
            ]),
            'exercises' => $this->miniExerciseService->getExercises([
                'published_only' => true,
                'search' => $filters['search'] ?? null,
                'mistake_type' => $filters['mistake_type'] ?? null,
                'exercise_type' => $filters['exercise_type'] ?? null,
            ]),
            'activeTab' => $filters['tab'] ?? 'ielts',
            'filters' => $filters,
        ]);
    }
}

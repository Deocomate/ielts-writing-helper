<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Services\LessonService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LessonController extends Controller
{
    public function __construct(private readonly LessonService $lessonService) {}

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'task_type' => ['nullable', 'in:task_1,task_2'],
            'status' => ['nullable', 'in:draft,published'],
        ]);

        $lessons = $this->lessonService->getLessons($filters);

        return view('admin.lessons.index', compact('lessons', 'filters'));
    }

    public function create(): View
    {
        return view('admin.lessons.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules());
        $image = $request->file('image');

        $this->lessonService->createLesson($data, $image);

        return redirect()->route('admin.lessons.index')->with('success', 'Bài học đã được tạo thành công.');
    }

    public function edit(Lesson $lesson): View
    {
        $lesson->load(['vocabularies' => fn ($query) => $query->orderBy('word')]);

        return view('admin.lessons.edit', compact('lesson'));
    }

    public function update(Request $request, Lesson $lesson): RedirectResponse
    {
        $data = $request->validate($this->rules());
        $image = $request->file('image');
        $removeImage = $request->boolean('remove_image');

        $this->lessonService->updateLesson($lesson, $data, $image, $removeImage);

        return redirect()->route('admin.lessons.index')->with('success', 'Bài học đã được cập nhật.');
    }

    public function destroy(Lesson $lesson): RedirectResponse
    {
        $this->lessonService->deleteLesson($lesson);

        return redirect()->route('admin.lessons.index')->with('success', 'Bài học đã được xóa.');
    }

    public function mapping(Lesson $lesson): View
    {
        $lesson->load('annotations');
        $essayHtml = $this->lessonService->renderEssayWithAnnotations($lesson);
        $normalizedEssay = $this->lessonService->getNormalizedEssay($lesson);

        return view('admin.lessons.mapping', compact('lesson', 'essayHtml', 'normalizedEssay'));
    }

    private function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'task_type' => ['required', 'in:task_1,task_2'],
            'question_type' => ['nullable', 'string', 'max:100'],
            'prompt_text' => ['required', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120'],
            'sample_essay' => ['required', 'string'],
            'band_score' => ['nullable', 'numeric', 'between:0,9'],
            'tr_score' => ['nullable', 'numeric', 'between:0,9'],
            'cc_score' => ['nullable', 'numeric', 'between:0,9'],
            'lr_score' => ['nullable', 'numeric', 'between:0,9'],
            'gra_score' => ['nullable', 'numeric', 'between:0,9'],
            'is_premium' => ['required', 'boolean'],
            'status' => ['required', 'in:draft,published'],
        ];
    }
}

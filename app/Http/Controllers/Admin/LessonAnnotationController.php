<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\LessonAnnotation;
use App\Services\LessonService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LessonAnnotationController extends Controller
{
    public function __construct(private readonly LessonService $lessonService) {}

    public function store(Request $request, Lesson $lesson): RedirectResponse
    {
        $data = $request->validate([
            'selected_text' => ['required', 'string', 'max:500'],
            'start_offset' => ['nullable', 'integer', 'min:0'],
            'end_offset' => ['nullable', 'integer', 'min:0'],
            'tag_type' => ['required', 'in:vocabulary,grammar,coherence,logic'],
            'access_tier' => ['required', 'in:free,pro'],
            'explanation' => ['required', 'string'],
        ]);

        $this->lessonService->createAnnotation($lesson, $data);

        return redirect()->route('admin.lessons.mapping', $lesson)
            ->with('success', 'Annotation đã được thêm.');
    }

    public function update(Request $request, Lesson $lesson, LessonAnnotation $annotation): RedirectResponse
    {
        $data = $request->validate([
            'selected_text' => ['required', 'string', 'max:500'],
            'start_offset' => ['nullable', 'integer', 'min:0'],
            'end_offset' => ['nullable', 'integer', 'min:0'],
            'tag_type' => ['required', 'in:vocabulary,grammar,coherence,logic'],
            'access_tier' => ['required', 'in:free,pro'],
            'explanation' => ['required', 'string'],
        ]);

        $this->lessonService->updateAnnotation($lesson, $annotation, $data);

        return redirect()->route('admin.lessons.mapping', $lesson)
            ->with('success', 'Annotation đã được cập nhật.');
    }

    public function destroy(Lesson $lesson, LessonAnnotation $annotation): RedirectResponse
    {
        $this->lessonService->deleteAnnotation($lesson, $annotation);

        return redirect()->route('admin.lessons.mapping', $lesson)
            ->with('success', 'Annotation đã được xóa.');
    }
}

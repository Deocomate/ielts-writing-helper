<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\LessonVocabulary;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LessonVocabularyController extends Controller
{
    public function store(Request $request, Lesson $lesson): RedirectResponse
    {
        $data = $request->validate($this->rules($lesson));

        $lesson->vocabularies()->create($data);

        return redirect()
            ->route('admin.lessons.edit', $lesson)
            ->withFragment('lesson-vocabulary-section')
            ->with('success', 'Từ vựng đã được thêm vào sổ tay bài học.');
    }

    public function update(Request $request, Lesson $lesson, LessonVocabulary $vocabulary): RedirectResponse
    {
        abort_unless($vocabulary->lesson_id === $lesson->id, 404);

        $data = $request->validate($this->rules($lesson, $vocabulary));
        $vocabulary->update($data);

        return redirect()
            ->route('admin.lessons.edit', $lesson)
            ->withFragment('lesson-vocabulary-section')
            ->with('success', 'Từ vựng đã được cập nhật.');
    }

    public function destroy(Lesson $lesson, LessonVocabulary $vocabulary): RedirectResponse
    {
        abort_unless($vocabulary->lesson_id === $lesson->id, 404);

        $vocabulary->delete();

        return redirect()
            ->route('admin.lessons.edit', $lesson)
            ->withFragment('lesson-vocabulary-section')
            ->with('success', 'Từ vựng đã được xóa khỏi bài học.');
    }

    private function rules(Lesson $lesson, ?LessonVocabulary $vocabulary = null): array
    {
        return [
            'word' => [
                'required',
                'string',
                'max:255',
                Rule::unique('lesson_vocabularies', 'word')
                    ->where('lesson_id', $lesson->id)
                    ->ignore($vocabulary?->id),
            ],
            'meaning_vi' => ['required', 'string', 'max:255'],
            'meaning_en' => ['nullable', 'string', 'max:255'],
            'example_sentence' => ['nullable', 'string'],
            'access_tier' => ['required', 'in:free,pro'],
        ];
    }
}
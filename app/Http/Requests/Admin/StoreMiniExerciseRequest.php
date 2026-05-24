<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreMiniExerciseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'mistake_type' => ['required', 'in:tense,structure,vocabulary,punctuation'],
            'exercise_type' => ['required', 'in:fill_blank,drag_drop,short_answer'],
            'difficulty_level' => ['required', 'in:easy,medium,hard'],
            'marked_sentence' => ['nullable', 'required_unless:exercise_type,short_answer', 'string', 'max:2000'],
            'distractors' => ['nullable', 'string', 'max:1000'],
            'prompt' => ['nullable', 'required_if:exercise_type,short_answer', 'string', 'max:2000'],
            'accepted_answers' => ['nullable', 'required_if:exercise_type,short_answer', 'string', 'max:1000'],
            'explanation' => ['required', 'string', 'max:5000'],
            'status' => ['required', 'in:draft,published'],
        ];
    }
}

<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreReadingMaterialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'topic' => ['required', 'in:people,environment,technology,inspiration'],
            'excerpt' => ['nullable', 'string', 'max:1000'],
            'content' => ['required', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120'],
            'vocabulary_notes' => ['nullable', 'array'],
            'vocabulary_notes.*.term' => ['nullable', 'string', 'max:120'],
            'vocabulary_notes.*.meaning' => ['nullable', 'string', 'max:255'],
            'vocabulary_notes.*.note' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'in:draft,published'],
        ];
    }
}

<?php

namespace App\Http\Requests\Admin;

class UpdateReadingMaterialRequest extends StoreReadingMaterialRequest
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'remove_image' => ['nullable', 'boolean'],
        ]);
    }
}

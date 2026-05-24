<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MiniExercise extends Model
{
    /** @use HasFactory<\Database\Factories\MiniExerciseFactory> */
    use HasFactory;

    public const TYPE_FILL_BLANK = 'fill_blank';

    public const TYPE_DRAG_DROP = 'drag_drop';

    public const TYPE_SHORT_ANSWER = 'short_answer';

    protected $fillable = [
        'title',
        'slug',
        'mistake_type',
        'exercise_type',
        'difficulty_level',
        'question_data',
        'explanation',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'question_data' => 'array',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}

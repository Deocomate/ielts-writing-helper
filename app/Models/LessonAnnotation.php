<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonAnnotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'lesson_id',
        'selected_text',
        'start_offset',
        'end_offset',
        'tag_type',
        'access_tier',
        'explanation',
    ];

    protected function casts(): array
    {
        return [
            'start_offset' => 'integer',
            'end_offset' => 'integer',
        ];
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }
}

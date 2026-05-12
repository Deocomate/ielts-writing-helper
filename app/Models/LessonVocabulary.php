<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonVocabulary extends Model
{
    use HasFactory;

    protected $fillable = [
        'lesson_id',
        'word',
        'meaning_vi',
        'meaning_en',
        'example_sentence',
        'access_tier',
    ];

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }
}

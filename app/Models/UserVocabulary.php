<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserVocabulary extends Model
{
    protected $table = 'user_vocabularies';

    protected $fillable = [
        'user_id',
        'lesson_id',
        'word',
        'meaning',
        'context_sentence',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }
}

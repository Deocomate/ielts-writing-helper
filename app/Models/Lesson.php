<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'task_type',
        'question_type',
        'prompt_text',
        'image_path',
        'sample_essay',
        'band_score',
        'tr_score',
        'cc_score',
        'lr_score',
        'gra_score',
        'is_premium',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'band_score' => 'decimal:1',
            'tr_score' => 'decimal:1',
            'cc_score' => 'decimal:1',
            'lr_score' => 'decimal:1',
            'gra_score' => 'decimal:1',
            'is_premium' => 'boolean',
        ];
    }

    public function annotations(): HasMany
    {
        return $this->hasMany(LessonAnnotation::class);
    }

    public function vocabularies(): HasMany
    {
        return $this->hasMany(LessonVocabulary::class);
    }
}

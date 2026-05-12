<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MockExam extends Model
{
    use HasFactory;

    public const STATUS_GRADING = 'grading';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'user_id',
        'lesson_id',
        'user_essay',
        'word_count',
        'time_taken_seconds',
        'status',
        'overall_band',
        'tr_score',
        'cc_score',
        'lr_score',
        'gra_score',
        'ai_feedback',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'overall_band' => 'decimal:1',
            'tr_score' => 'decimal:1',
            'cc_score' => 'decimal:1',
            'lr_score' => 'decimal:1',
            'gra_score' => 'decimal:1',
            'ai_feedback' => 'array',
            'submitted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function isGrading(): bool
    {
        return $this->status === self::STATUS_GRADING;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }
}

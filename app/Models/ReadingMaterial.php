<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReadingMaterial extends Model
{
    /** @use HasFactory<\Database\Factories\ReadingMaterialFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'topic',
        'excerpt',
        'content',
        'image_path',
        'vocabulary_notes',
        'status',
        'views_count',
    ];

    protected function casts(): array
    {
        return [
            'vocabulary_notes' => 'array',
            'views_count' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}

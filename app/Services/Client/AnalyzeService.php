<?php

namespace App\Services\Client;

use App\Models\Lesson;
use App\Services\LessonService;

class AnalyzeService
{
    public function __construct(private readonly LessonService $lessonService) {}

    /**
     * Get lesson data prepared for analyze mode.
     */
    public function getLessonForAnalysis(int $lessonId, bool $isProUser): array
    {
        $lesson = Lesson::where('status', 'published')
            ->with(['annotations', 'vocabularies'])
            ->findOrFail($lessonId);

        $visibleAnnotations = $isProUser
            ? $lesson->annotations->values()
            : $lesson->annotations->where('access_tier', 'free')->values();

        $visibleVocabularies = $isProUser
            ? $lesson->vocabularies->values()
            : $lesson->vocabularies->where('access_tier', 'free')->values();

        $lesson->setRelation('annotations', $visibleAnnotations);
        $lesson->setRelation('vocabularies', $visibleVocabularies);

        $annotatedHtml = $this->lessonService->renderEssayWithAnnotations($lesson, true);

        $annotationsGrouped = $visibleAnnotations->groupBy('tag_type');

        return [
            'lesson'         => $lesson,
            'annotatedHtml'  => $annotatedHtml,
            'annotations'    => $visibleAnnotations,
            'vocabularies'   => $visibleVocabularies,
            'stats'          => [
                'vocabulary'  => $annotationsGrouped->get('vocabulary', collect())->count(),
                'grammar'     => $annotationsGrouped->get('grammar', collect())->count(),
                'coherence'   => $annotationsGrouped->get('coherence', collect())->count(),
                'logic'       => $annotationsGrouped->get('logic', collect())->count(),
            ],
        ];
    }
}

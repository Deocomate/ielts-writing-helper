<?php

namespace App\Services;

use App\Models\Lesson;
use App\Models\LessonAnnotation;
use App\Services\FileUploadService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class LessonService
{
    public function __construct(private readonly FileUploadService $fileUploadService) {}

    public function getLessons(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = Lesson::query();

        if (!empty($filters['search'])) {
            $query->where('title', 'like', '%' . $filters['search'] . '%');
        }

        if (!empty($filters['task_type'])) {
            $query->where('task_type', $filters['task_type']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate($perPage)->withQueryString();
    }

    public function createLesson(array $data, ?UploadedFile $image = null): Lesson
    {
        if ($image) {
            $data['image_path'] = $this->fileUploadService->upload($image, 'lessons');
        }

        return Lesson::create($data);
    }

    public function updateLesson(Lesson $lesson, array $data, ?UploadedFile $image = null, bool $removeImage = false): Lesson
    {
        $oldImagePath = $lesson->image_path;
        $uploadedImagePath = null;

        if ($image) {
            $uploadedImagePath = $this->fileUploadService->upload($image, 'lessons');
            $data['image_path'] = $uploadedImagePath;
        } elseif ($removeImage) {
            $data['image_path'] = null;
        }

        try {
            $lesson->update($data);
        } catch (Throwable $throwable) {
            if ($uploadedImagePath) {
                $this->fileUploadService->delete($uploadedImagePath);
            }

            throw $throwable;
        }

        if (($removeImage || $image) && $oldImagePath && $oldImagePath !== ($data['image_path'] ?? null)) {
            $this->fileUploadService->delete($oldImagePath);
        }

        return $lesson->fresh();
    }

    public function deleteLesson(Lesson $lesson): void
    {
        // Delete associated image
        if ($lesson->image_path) {
            $this->fileUploadService->delete($lesson->image_path);
        }

        $lesson->delete();
    }

    public function createAnnotation(Lesson $lesson, array $data): LessonAnnotation
    {
        $normalizedData = $this->normalizeAnnotationPayload($lesson, $data);

        return $lesson->annotations()->create($normalizedData);
    }

    public function updateAnnotation(Lesson $lesson, LessonAnnotation $annotation, array $data): LessonAnnotation
    {
        abort_unless($annotation->lesson_id === $lesson->id, 404);

        $normalizedData = $this->normalizeAnnotationPayload($lesson, $data);
        $annotation->update($normalizedData);

        return $annotation->fresh();
    }

    public function deleteAnnotation(Lesson $lesson, LessonAnnotation $annotation): void
    {
        abort_unless($annotation->lesson_id === $lesson->id, 404);
        $annotation->delete();
    }

    /**
     * Get normalized essay text (consistent \n line endings).
     */
    public function getNormalizedEssay(Lesson $lesson): string
    {
        return str_replace(["\r\n", "\r"], "\n", $lesson->sample_essay ?? '');
    }

    /**
     * Render essay text with annotation highlights as HTML spans.
     * Annotations are sorted and non-overlapping segments are wrapped in colored spans.
     * Uses normalized text (\n only) for consistent offset mapping with JS.
     */
    public function renderEssayWithAnnotations(Lesson $lesson, bool $withClientTooltips = false): string
    {
        $essay = $this->getNormalizedEssay($lesson);
        $annotations = $lesson->annotations->sortBy('start_offset')->values();

        if ($annotations->isEmpty()) {
            return nl2br(e($essay));
        }

        $html = '';
        $lastPos = 0;

        foreach ($annotations as $annotation) {
            $start = (int) $annotation->start_offset;
            $end = (int) $annotation->end_offset;

            // Skip invalid or overlapping annotations
            if ($start < $lastPos || $start >= $end || $end > mb_strlen($essay)) {
                continue;
            }

            // Plain text before this annotation
            if ($start > $lastPos) {
                $html .= nl2br(e(mb_substr($essay, $lastPos, $start - $lastPos)));
            }

            // Annotated span (apply nl2br for consistent line break rendering)
            $annotatedText = mb_substr($essay, $start, $end - $start);
            $tag = e($annotation->tag_type);
            $title = e(Str::limit($annotation->explanation, 80));
            $clientClass = match ($annotation->tag_type) {
                'vocabulary' => 'underline-vocabulary',
                'grammar' => 'underline-grammar',
                'coherence' => 'underline-coherence',
                default => 'underline-logic',
            };

            $spanClass = $withClientTooltips
                ? $clientClass
                : 'annotation-highlight';

            $tooltipHtml = '';
            if ($withClientTooltips) {
                $tooltipHtml = '<span class="tooltip-card">' . e($annotation->explanation) . '</span>';
            }

            $html .= '<span class="' . $spanClass . '" '
                   . 'data-annotation-id="' . $annotation->id . '" '
                   . 'data-tag="' . $tag . '" '
                   . 'title="' . $title . '">'
                   . nl2br(e($annotatedText))
                   . $tooltipHtml
                   . '</span>';

            $lastPos = $end;
        }

        // Remaining text after last annotation
        if ($lastPos < mb_strlen($essay)) {
            $html .= nl2br(e(mb_substr($essay, $lastPos)));
        }

        return $html;
    }

    /**
     * Ensure selected text and offsets are valid against normalized essay content.
     * This prevents malformed or tampered payloads from being saved.
     *
     * @throws ValidationException
     */
    private function normalizeAnnotationPayload(Lesson $lesson, array $data): array
    {
        $essay = $this->getNormalizedEssay($lesson);
        $essayLength = mb_strlen($essay);

        $selectedText = str_replace(["\r\n", "\r"], "\n", (string) ($data['selected_text'] ?? ''));
        $selectedText = trim($selectedText);

        if ($selectedText === '') {
            throw ValidationException::withMessages([
                'selected_text' => 'Đoạn text đã chọn không hợp lệ. Vui lòng bôi đen lại.',
            ]);
        }

        $startOffset = is_numeric($data['start_offset'] ?? null) ? (int) $data['start_offset'] : null;
        $endOffset = is_numeric($data['end_offset'] ?? null) ? (int) $data['end_offset'] : null;
        $textLength = mb_strlen($selectedText);

        $isOffsetInRange = $startOffset !== null
            && $endOffset !== null
            && $startOffset >= 0
            && $endOffset > $startOffset
            && $endOffset <= $essayLength;

        if ($isOffsetInRange) {
            $slice = mb_substr($essay, $startOffset, $endOffset - $startOffset);
            if ($slice === $selectedText) {
                $data['selected_text'] = $selectedText;
                $data['start_offset'] = $startOffset;
                $data['end_offset'] = $endOffset;

                return $data;
            }
        }

        $occurrences = $this->findTextOccurrences($essay, $selectedText);
        if (empty($occurrences)) {
            throw ValidationException::withMessages([
                'selected_text' => 'Không tìm thấy đoạn text trong bài mẫu. Vui lòng chọn lại chính xác.',
            ]);
        }

        $resolvedStart = null;

        if (count($occurrences) === 1) {
            $resolvedStart = $occurrences[0];
        } elseif ($startOffset !== null) {
            usort($occurrences, fn (int $left, int $right): int => abs($left - $startOffset) <=> abs($right - $startOffset));

            $closestDistance = abs($occurrences[0] - $startOffset);
            $secondDistance = abs($occurrences[1] - $startOffset);

            if ($closestDistance !== $secondDistance) {
                $resolvedStart = $occurrences[0];
            }
        }

        if ($resolvedStart === null) {
            throw ValidationException::withMessages([
                'selected_text' => 'Không thể xác định duy nhất vị trí offset. Vui lòng bôi đen lại đoạn text cụ thể hơn.',
            ]);
        }

        $resolvedEnd = $resolvedStart + $textLength;
        if ($resolvedEnd > $essayLength) {
            throw ValidationException::withMessages([
                'selected_text' => 'Offset vượt quá độ dài bài mẫu. Vui lòng chọn lại đoạn text.',
            ]);
        }

        $data['selected_text'] = $selectedText;
        $data['start_offset'] = $resolvedStart;
        $data['end_offset'] = $resolvedEnd;

        return $data;
    }

    /**
     * Return all start offsets for an exact text match in essay.
     *
     * @return array<int>
     */
    private function findTextOccurrences(string $essay, string $selectedText): array
    {
        $occurrences = [];
        $offset = 0;

        while (true) {
            $position = mb_strpos($essay, $selectedText, $offset);
            if ($position === false) {
                break;
            }

            $occurrences[] = $position;
            $offset = $position + 1;
        }

        return $occurrences;
    }
}

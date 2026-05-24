<?php

namespace App\Services;

use App\Models\ReadingMaterial;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Throwable;

class ReadingMaterialService
{
    public function __construct(private readonly FileUploadService $fileUploadService) {}

    public function getMaterials(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        $query = ReadingMaterial::query();

        if (! empty($filters['published_only'])) {
            $query->where('status', 'published');
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($builder) use ($search): void {
                $builder->where('title', 'like', '%'.$search.'%')
                    ->orWhere('excerpt', 'like', '%'.$search.'%');
            });
        }

        if (! empty($filters['topic'])) {
            $query->where('topic', $filters['topic']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate($perPage)->withQueryString();
    }

    public function createMaterial(array $data, ?UploadedFile $image = null): ReadingMaterial
    {
        $data = $this->normalizePayload($data);

        if ($image) {
            $data['image_path'] = $this->fileUploadService->upload($image, 'reading-materials');
        }

        return ReadingMaterial::query()->create($data);
    }

    public function updateMaterial(ReadingMaterial $material, array $data, ?UploadedFile $image = null, bool $removeImage = false): ReadingMaterial
    {
        $data = $this->normalizePayload($data, $material);
        $oldImagePath = $material->image_path;
        $uploadedImagePath = null;

        if ($image) {
            $uploadedImagePath = $this->fileUploadService->upload($image, 'reading-materials');
            $data['image_path'] = $uploadedImagePath;
        } elseif ($removeImage) {
            $data['image_path'] = null;
        }

        try {
            $material->update($data);
        } catch (Throwable $throwable) {
            if ($uploadedImagePath) {
                $this->fileUploadService->delete($uploadedImagePath);
            }

            throw $throwable;
        }

        if (($removeImage || $image) && $oldImagePath && $oldImagePath !== ($data['image_path'] ?? null)) {
            $this->fileUploadService->delete($oldImagePath);
        }

        return $material->fresh();
    }

    public function deleteMaterial(ReadingMaterial $material): void
    {
        $this->fileUploadService->delete($material->image_path);
        $material->delete();
    }

    public function getPublishedBySlug(string $slug): ReadingMaterial
    {
        return ReadingMaterial::query()
            ->where('status', 'published')
            ->where('slug', $slug)
            ->firstOrFail();
    }

    private function normalizePayload(array $data, ?ReadingMaterial $material = null): array
    {
        $data['slug'] = $this->uniqueSlug($data['title'], $material?->id);
        $data['vocabulary_notes'] = $this->normalizeVocabularyNotes($data['vocabulary_notes'] ?? []);

        return $data;
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug !== '' ? $baseSlug : Str::random(8);
        $counter = 2;

        while (ReadingMaterial::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    private function normalizeVocabularyNotes(array $notes): array
    {
        return collect($notes)
            ->map(fn (array $note): array => [
                'term' => trim((string) ($note['term'] ?? '')),
                'meaning' => trim((string) ($note['meaning'] ?? '')),
                'note' => trim((string) ($note['note'] ?? '')),
            ])
            ->filter(fn (array $note): bool => $note['term'] !== '' && $note['meaning'] !== '')
            ->values()
            ->all();
    }
}

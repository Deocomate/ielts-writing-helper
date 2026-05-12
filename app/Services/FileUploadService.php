<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadService
{
    /**
     * Upload a file to the public disk.
     *
     * @param UploadedFile $file  The uploaded file instance.
     * @param string       $directory  Subdirectory inside storage/app/public.
     * @return string  The relative path stored in DB (e.g. "lessons/abc123.png").
     */
    public function upload(UploadedFile $file, string $directory = 'lessons'): string
    {
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();

        return $file->storeAs($directory, $filename, 'public');
    }

    /**
     * Delete a file from the public disk.
     *
     * @param string|null $path  The relative path stored in DB.
     */
    public function delete(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    /**
     * Get the public URL of an uploaded file.
     *
     * @param string|null $path  The relative path stored in DB.
     * @return string|null  Full public URL or null.
     */
    public function url(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }
}

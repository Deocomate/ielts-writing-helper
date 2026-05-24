<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ReadingMaterial;
use App\Services\ReadingMaterialService;
use Illuminate\View\View;

class ReadingMaterialController extends Controller
{
    public function __construct(private readonly ReadingMaterialService $readingMaterialService) {}

    public function show(ReadingMaterial $readingMaterial): View
    {
        $material = $this->readingMaterialService->getPublishedBySlug($readingMaterial->slug);
        $material->increment('views_count');

        return view('client.reading-materials.show', [
            'material' => $material->fresh(),
        ]);
    }
}

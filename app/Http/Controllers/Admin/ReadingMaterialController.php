<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreReadingMaterialRequest;
use App\Http\Requests\Admin\UpdateReadingMaterialRequest;
use App\Models\ReadingMaterial;
use App\Services\ReadingMaterialService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReadingMaterialController extends Controller
{
    public function __construct(private readonly ReadingMaterialService $readingMaterialService) {}

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'topic' => ['nullable', 'in:people,environment,technology,inspiration'],
            'status' => ['nullable', 'in:draft,published'],
        ]);

        return view('admin.reading-materials.index', [
            'materials' => $this->readingMaterialService->getMaterials($filters, 15),
            'filters' => $filters,
            'topics' => $this->topics(),
        ]);
    }

    public function create(): View
    {
        return view('admin.reading-materials.create', [
            'material' => null,
            'topics' => $this->topics(),
        ]);
    }

    public function store(StoreReadingMaterialRequest $request): RedirectResponse
    {
        $this->readingMaterialService->createMaterial($request->validated(), $request->file('image'));

        return redirect()->route('admin.reading-materials.index')->with('success', 'Học liệu đã được tạo thành công.');
    }

    public function edit(ReadingMaterial $readingMaterial): View
    {
        return view('admin.reading-materials.edit', [
            'material' => $readingMaterial,
            'topics' => $this->topics(),
        ]);
    }

    public function update(UpdateReadingMaterialRequest $request, ReadingMaterial $readingMaterial): RedirectResponse
    {
        $this->readingMaterialService->updateMaterial(
            $readingMaterial,
            $request->validated(),
            $request->file('image'),
            $request->boolean('remove_image')
        );

        return redirect()->route('admin.reading-materials.index')->with('success', 'Học liệu đã được cập nhật.');
    }

    public function destroy(ReadingMaterial $readingMaterial): RedirectResponse
    {
        $this->readingMaterialService->deleteMaterial($readingMaterial);

        return redirect()->route('admin.reading-materials.index')->with('success', 'Học liệu đã được xóa.');
    }

    private function topics(): array
    {
        return [
            'people' => 'Con người',
            'environment' => 'Môi trường',
            'technology' => 'Công nghệ',
            'inspiration' => 'Truyền cảm hứng',
        ];
    }
}

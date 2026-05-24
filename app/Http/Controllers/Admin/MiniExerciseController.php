<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreMiniExerciseRequest;
use App\Http\Requests\Admin\UpdateMiniExerciseRequest;
use App\Models\MiniExercise;
use App\Services\MiniExerciseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MiniExerciseController extends Controller
{
    public function __construct(private readonly MiniExerciseService $miniExerciseService) {}

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'mistake_type' => ['nullable', 'in:tense,structure,vocabulary,punctuation'],
            'exercise_type' => ['nullable', 'in:fill_blank,drag_drop,short_answer'],
            'status' => ['nullable', 'in:draft,published'],
        ]);

        return view('admin.mini-exercises.index', [
            'exercises' => $this->miniExerciseService->getExercises($filters, 15),
            'filters' => $filters,
            'mistakeTypes' => $this->mistakeTypes(),
            'exerciseTypes' => $this->exerciseTypes(),
            'difficultyLevels' => $this->difficultyLevels(),
        ]);
    }

    public function create(): View
    {
        return view('admin.mini-exercises.create', [
            'exercise' => null,
            'mistakeTypes' => $this->mistakeTypes(),
            'exerciseTypes' => $this->exerciseTypes(),
            'difficultyLevels' => $this->difficultyLevels(),
        ]);
    }

    public function store(StoreMiniExerciseRequest $request): RedirectResponse
    {
        $this->miniExerciseService->createExercise($request->validated());

        return redirect()->route('admin.mini-exercises.index')->with('success', 'Bài tập đã được tạo thành công.');
    }

    public function edit(MiniExercise $miniExercise): View
    {
        return view('admin.mini-exercises.edit', [
            'exercise' => $miniExercise,
            'mistakeTypes' => $this->mistakeTypes(),
            'exerciseTypes' => $this->exerciseTypes(),
            'difficultyLevels' => $this->difficultyLevels(),
        ]);
    }

    public function update(UpdateMiniExerciseRequest $request, MiniExercise $miniExercise): RedirectResponse
    {
        $this->miniExerciseService->updateExercise($miniExercise, $request->validated());

        return redirect()->route('admin.mini-exercises.index')->with('success', 'Bài tập đã được cập nhật.');
    }

    public function destroy(MiniExercise $miniExercise): RedirectResponse
    {
        $miniExercise->delete();

        return redirect()->route('admin.mini-exercises.index')->with('success', 'Bài tập đã được xóa.');
    }

    private function mistakeTypes(): array
    {
        return [
            'tense' => 'Sai thì',
            'structure' => 'Cấu trúc',
            'vocabulary' => 'Từ vựng',
            'punctuation' => 'Dấu câu',
        ];
    }

    private function exerciseTypes(): array
    {
        return [
            'fill_blank' => 'Điền từ',
            'drag_drop' => 'Kéo thả',
            'short_answer' => 'Trả lời ngắn',
        ];
    }

    private function difficultyLevels(): array
    {
        return [
            'easy' => 'Dễ',
            'medium' => 'Trung bình',
            'hard' => 'Khó',
        ];
    }
}

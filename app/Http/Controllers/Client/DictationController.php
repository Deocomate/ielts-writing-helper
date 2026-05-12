<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\Client\DictationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DictationController extends Controller
{
    public function __construct(private readonly DictationService $dictationService) {}

    public function show(int $lesson): View
    {
        $lessonData = $this->dictationService->getLessonForDictation($lesson);

        if ($lessonData->is_premium && ! auth()->user()->isPro()) {
            abort(403, 'Bài học này yêu cầu tài khoản Pro.');
        }

        return view('client.learning.study-dictation', [
            'lesson' => $lessonData,
        ]);
    }

    public function report(int $history): View
    {
        return view('client.learning.dictation-report', [
            'history' => $this->dictationService->getResult($history, auth()->user()),
        ]);
    }

    public function saveResult(Request $request): JsonResponse
    {
        $data = $request->validate([
            'lesson_id' => ['required', 'exists:lessons,id'],
            'wpm' => ['required', 'numeric', 'min:0'],
            'accuracy' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $result = $this->dictationService->saveResult(auth()->user(), $data);

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }
}

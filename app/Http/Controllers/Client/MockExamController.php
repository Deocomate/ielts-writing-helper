<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\Client\MockExamService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class MockExamController extends Controller
{
    public function __construct(private readonly MockExamService $examService) {}

    public function intro(int $lesson): View
    {
        $user = auth()->user();
        if (!$user->isPro()) {
            abort(403, 'Thi thử yêu cầu tài khoản Pro.');
        }

        return view('client.learning.mock-exam-intro', [
            'lesson' => $this->examService->getLessonForExam($lesson),
        ]);
    }

    public function room(int $lesson): View
    {
        $user = auth()->user();
        if (!$user->isPro()) {
            abort(403, 'Thi thử yêu cầu tài khoản Pro.');
        }

        return view('client.learning.mock-exam-room', [
            'lesson' => $this->examService->getLessonForExam($lesson),
        ]);
    }

    public function submit(Request $request): RedirectResponse
    {
        $user = auth()->user();
        if (!$user->isPro()) {
            abort(403, 'Thi thử yêu cầu tài khoản Pro.');
        }

        $data = $request->validate([
            'lesson_id'          => ['required', 'exists:lessons,id'],
            'user_essay'         => ['required', 'string', 'min:10', 'max:12000'],
            'time_taken_seconds' => ['required', 'integer', 'min:0'],
        ]);

        $exam = $this->examService->submitExam($user, $data);

        return redirect()->route('client.learning.mock-exam.report', $exam->id)
            ->with('success', 'Bài thi đã được gửi. AI đang chấm điểm, vui lòng chờ trong giây lát.');
    }

    public function report(int $exam): View
    {
        $result = $this->examService->getExamResult($exam, auth()->user());

        return view('client.learning.mock-exam-report', [
            'exam' => $result,
        ]);
    }

    public function status(int $exam): JsonResponse
    {
        return response()->json(
            $this->examService->getExamStatusData($exam, auth()->user())
        );
    }
}

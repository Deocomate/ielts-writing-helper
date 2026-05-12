<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\Client\ClientDashboardService;
use App\Services\Client\ProfileService;
use App\Services\Client\VocabularyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly ClientDashboardService $dashboardService,
        private readonly ProfileService $profileService,
        private readonly VocabularyService $vocabularyService,
    ) {}

    public function index(Request $request): View
    {
        $user = auth()->user();
        $filters = $request->validate([
            'period_days' => ['nullable', 'integer', 'in:7,30,90,180,365'],
        ]);
        $periodDays = (int) ($filters['period_days'] ?? 30);

        return view('client.dashboard.index', [
            'stats' => $this->dashboardService->getStats($user),
            'analytics' => $this->dashboardService->getAnalytics($user, $periodDays),
            'periodDays' => $periodDays,
            'recentActivity' => $this->dashboardService->getRecentActivity($user),
            'recommendedLessons' => $this->dashboardService->getRecommendedLessons($user),
        ]);
    }

    public function profile(): View
    {
        return view('client.dashboard.profile');
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.auth()->id()],
        ]);

        $this->profileService->updateProfile(auth()->user(), $data);

        return back()->with('success', 'Cập nhật thông tin thành công!');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $this->profileService->updatePassword(auth()->user(), $request->input('password'));

        return back()->with('success', 'Đổi mật khẩu thành công!');
    }

    public function vocabulary(Request $request): View
    {
        $user = auth()->user();

        return view('client.dashboard.vocabulary', [
            'vocabularies' => $this->vocabularyService->getVocabularies($user, $request->all()),
            'totalCount' => $this->vocabularyService->getCount($user),
        ]);
    }

    public function saveVocabulary(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'word' => ['required', 'string', 'max:255'],
            'meaning' => ['nullable', 'string', 'max:500'],
            'lesson_id' => ['nullable', 'exists:lessons,id'],
            'context_sentence' => ['nullable', 'string', 'max:500'],
        ]);

        $data['word'] = trim($data['word']);
        $data['meaning'] = trim((string) ($data['meaning'] ?? ''));

        if ($data['meaning'] === '') {
            $data['meaning'] = 'Chưa có nghĩa';
        }

        $vocabulary = $this->vocabularyService->saveVocabulary(auth()->user(), $data);
        $isCreated = $vocabulary->wasRecentlyCreated;
        $message = $isCreated ? 'Đã lưu từ vựng!' : 'Từ vựng đã có trong sổ tay.';

        if ($request->expectsJson()) {
            return response()->json([
                'status' => $isCreated ? 'created' : 'already_exists',
                'message' => $message,
                'vocabulary' => [
                    'id' => $vocabulary->id,
                    'word' => $vocabulary->word,
                    'meaning' => $vocabulary->meaning,
                ],
            ], $isCreated ? 201 : 200);
        }

        return back()->with('success', $message);
    }

    public function deleteVocabulary(int $id): RedirectResponse
    {
        $this->vocabularyService->deleteVocabulary(auth()->user(), $id);

        return back()->with('success', 'Đã xóa từ vựng.');
    }

    public function billing(): View
    {
        $user = auth()->user();

        return view('client.dashboard.billing', [
            'user' => $user,
            'transactions' => $user->transactions()->with('plan')->latest()->paginate(10),
        ]);
    }
}

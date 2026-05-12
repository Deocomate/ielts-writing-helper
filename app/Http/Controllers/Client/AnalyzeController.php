<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\Client\AnalyzeService;
use Illuminate\View\View;

class AnalyzeController extends Controller
{
    public function __construct(private readonly AnalyzeService $analyzeService) {}

    public function show(int $lesson): View
    {
        $data = $this->analyzeService->getLessonForAnalysis($lesson, auth()->user()->isPro());

        return view('client.learning.study-analyze', $data);
    }
}

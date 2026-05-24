<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\MiniExercise;
use App\Services\MiniExerciseService;
use Illuminate\View\View;

class MiniExerciseController extends Controller
{
    public function __construct(private readonly MiniExerciseService $miniExerciseService) {}

    public function show(MiniExercise $miniExercise): View
    {
        return view('client.mini-exercises.show', [
            'exercise' => $this->miniExerciseService->getPublishedBySlug($miniExercise->slug),
        ]);
    }
}

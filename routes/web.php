<?php

use App\Http\Controllers\Admin\AiAssistantController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\LessonAnnotationController;
use App\Http\Controllers\Admin\LessonController;
use App\Http\Controllers\Admin\LessonVocabularyController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Client\AiChatController;
use App\Http\Controllers\Client\AnalyzeController;
use App\Http\Controllers\Client\AuthController as ClientAuthController;
use App\Http\Controllers\Client\CheckoutController;
use App\Http\Controllers\Client\DashboardController as ClientDashboardController;
use App\Http\Controllers\Client\DictationController;
use App\Http\Controllers\Client\HomeController;
use App\Http\Controllers\Client\LessonController as ClientLessonController;
use App\Http\Controllers\Client\MockExamController;
use App\Http\Controllers\Client\SocialAuthController;
use Illuminate\Support\Facades\Route;

// ─────────────────────────────────────────────────────────────────────────────
// Client routes
// ─────────────────────────────────────────────────────────────────────────────
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('gioi-thieu', [HomeController::class, 'about'])->name('client.home.about');
Route::get('chuc-nang', [HomeController::class, 'features'])->name('client.home.features');
Route::get('goi-lien-he', [HomeController::class, 'pricingAndContact'])->name('client.home.pricing-contact');
Route::post('checkout/payos/webhook', [CheckoutController::class, 'webhook'])->name('client.checkout.payos.webhook');

Route::middleware('guest')->group(function () {
    Route::get('login', [ClientAuthController::class, 'showLogin'])->name('login');
    Route::post('login', [ClientAuthController::class, 'login'])->name('login.submit');

    Route::get('auth/{provider}/redirect', [SocialAuthController::class, 'redirect'])->name('social.redirect');
    Route::get('auth/{provider}/callback', [SocialAuthController::class, 'callback'])->name('social.callback');

    Route::get('register', [ClientAuthController::class, 'showRegister'])->name('register');
    Route::post('register', [ClientAuthController::class, 'register'])->name('register.submit');

    Route::get('forgot-password', [ClientAuthController::class, 'showForgotPassword'])->name('forgot-password');
    Route::post('forgot-password', [ClientAuthController::class, 'forgotPassword'])->name('forgot-password.submit');

    Route::get('reset-password/{token}', [ClientAuthController::class, 'showResetPassword'])->name('reset-password');
    Route::post('reset-password', [ClientAuthController::class, 'resetPassword'])->name('reset-password.submit');
});

Route::get('ai-chat/config', [AiChatController::class, 'config'])
    ->name('client.ai-chat.config');
Route::post('ai-chat/message', [AiChatController::class, 'message'])
    ->middleware('throttle:20,1')
    ->name('client.ai-chat.message');
Route::post('ai-chat/reset', [AiChatController::class, 'reset'])
    ->name('client.ai-chat.reset');

Route::middleware('auth')->group(function () {
    Route::post('logout', [ClientAuthController::class, 'logout'])->name('logout');

    // ── Dashboard ─────────────────────────────────────────────────────────
    Route::get('dashboard', [ClientDashboardController::class, 'index'])->name('client.dashboard');

    Route::get('dashboard/profile', [ClientDashboardController::class, 'profile'])->name('client.profile');
    Route::put('dashboard/profile', [ClientDashboardController::class, 'updateProfile'])->name('client.profile.update');
    Route::put('dashboard/password', [ClientDashboardController::class, 'updatePassword'])->name('client.password.update');

    Route::get('dashboard/vocabulary', [ClientDashboardController::class, 'vocabulary'])->name('client.vocabulary');
    Route::post('dashboard/vocabulary', [ClientDashboardController::class, 'saveVocabulary'])->name('client.vocabulary.store');
    Route::delete('dashboard/vocabulary/{id}', [ClientDashboardController::class, 'deleteVocabulary'])->name('client.vocabulary.destroy');

    Route::get('dashboard/billing', [ClientDashboardController::class, 'billing'])->name('client.billing');

    // ── Learning ──────────────────────────────────────────────────────────
    Route::get('lessons', [ClientLessonController::class, 'library'])->name('client.lessons.library');

    Route::get('learning/dictation/{lesson}', [DictationController::class, 'show'])
        ->whereNumber('lesson')
        ->name('client.learning.dictation');
    Route::get('learning/dictation/{history}/report', [DictationController::class, 'report'])
        ->whereNumber('history')
        ->name('client.learning.dictation.report');
    Route::post('learning/dictation/save', [DictationController::class, 'saveResult'])->name('client.learning.dictation.save');

    Route::get('learning/analyze/{lesson}', [AnalyzeController::class, 'show'])->name('client.learning.analyze');

    Route::get('learning/mock-exam/{lesson}/intro', [MockExamController::class, 'intro'])->name('client.learning.mock-exam.intro');
    Route::get('learning/mock-exam/{lesson}/room', [MockExamController::class, 'room'])->name('client.learning.mock-exam.room');
    Route::post('learning/mock-exam/submit', [MockExamController::class, 'submit'])->name('client.learning.mock-exam.submit');
    Route::get('learning/mock-exam/{exam}/report', [MockExamController::class, 'report'])->name('client.learning.mock-exam.report');
    Route::get('learning/mock-exam/{exam}/status', [MockExamController::class, 'status'])->name('client.learning.mock-exam.status');

    // ── Checkout ──────────────────────────────────────────────────────────
    Route::get('checkout', [CheckoutController::class, 'index'])->name('client.checkout');
    Route::post('checkout', [CheckoutController::class, 'process'])->name('client.checkout.process');
    Route::get('checkout/{transaction}/success', [CheckoutController::class, 'success'])->name('client.checkout.success');
    Route::get('checkout/{transaction}/failed', [CheckoutController::class, 'failed'])->name('client.checkout.failed');
    Route::get('checkout/{transaction}/pending', [CheckoutController::class, 'pending'])->name('client.checkout.pending');
});

// ─────────────────────────────────────────────────────────────────────────────
// Admin routes
// ─────────────────────────────────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->group(function () {

    // ── Guest routes (unauthenticated only) ──────────────────────────────────
    Route::middleware('guest')->group(function () {
        Route::get('login', [AuthController::class, 'showLogin'])
            ->name('auth.login');
        Route::post('login', [AuthController::class, 'login'])
            ->name('auth.login.submit');

        Route::get('forgot-password', [AuthController::class, 'showForgotPassword'])
            ->name('auth.forgot-password');
        Route::post('forgot-password', [AuthController::class, 'forgotPassword'])
            ->name('auth.forgot-password.submit');

        Route::get('reset-password/{token}', [AuthController::class, 'showResetPassword'])
            ->name('auth.reset-password');
        Route::post('reset-password', [AuthController::class, 'resetPassword'])
            ->name('auth.reset-password.submit');
    });

    // ── Authenticated routes ──────────────────────────────────────────────────
    Route::middleware('auth')->group(function () {
        // ── Superadmin-only: account management ──────────────────────────────
        Route::middleware('role:superadmin')->group(function () {
            Route::get('users', [UserController::class, 'index'])->name('users.index');
            Route::get('users/create', [UserController::class, 'create'])->name('users.create');
            Route::post('users', [UserController::class, 'store'])->name('users.store');
            Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
            Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
            Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        });

        Route::middleware('role:superadmin,admin')->group(function () {
            Route::get('/', fn () => redirect()->route('admin.dashboard'))->name('home');
            Route::get('dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
            Route::post('logout', [AuthController::class, 'logout'])->name('auth.logout');

            Route::get('ai-assistant', [AiAssistantController::class, 'edit'])->name('ai-assistant.edit');
            Route::put('ai-assistant', [AiAssistantController::class, 'update'])->name('ai-assistant.update');

            Route::get('clients', [ClientController::class, 'index'])->name('clients.index');
            Route::get('clients/{client}', [ClientController::class, 'show'])->name('clients.show');
            Route::put('clients/{client}/status', [ClientController::class, 'updateStatus'])->name('clients.update-status');
            Route::put('clients/{client}/subscription', [ClientController::class, 'updateSubscription'])->name('clients.update-subscription');

            Route::resource('lessons', LessonController::class)->except(['show']);
            Route::get('lessons/{lesson}/mapping', [LessonController::class, 'mapping'])->name('lessons.mapping');
            Route::post('lessons/{lesson}/annotations', [LessonAnnotationController::class, 'store'])->name('lessons.annotations.store');
            Route::put('lessons/{lesson}/annotations/{annotation}', [LessonAnnotationController::class, 'update'])->name('lessons.annotations.update');
            Route::delete('lessons/{lesson}/annotations/{annotation}', [LessonAnnotationController::class, 'destroy'])->name('lessons.annotations.destroy');
            Route::post('lessons/{lesson}/vocabularies', [LessonVocabularyController::class, 'store'])->name('lessons.vocabularies.store');
            Route::put('lessons/{lesson}/vocabularies/{vocabulary}', [LessonVocabularyController::class, 'update'])->name('lessons.vocabularies.update');
            Route::delete('lessons/{lesson}/vocabularies/{vocabulary}', [LessonVocabularyController::class, 'destroy'])->name('lessons.vocabularies.destroy');

            Route::get('transactions', [TransactionController::class, 'index'])->name('transactions.index');
            Route::put('transactions/{transaction}/status', [TransactionController::class, 'updateStatus'])->name('transactions.update-status');
            Route::delete('transactions/{transaction}', [TransactionController::class, 'destroy'])->name('transactions.destroy');

            Route::get('plans', [PlanController::class, 'index'])->name('plans.index');
            Route::get('plans/create', [PlanController::class, 'create'])->name('plans.create');
            Route::post('plans', [PlanController::class, 'store'])->name('plans.store');
            Route::get('plans/{plan}/edit', [PlanController::class, 'edit'])->name('plans.edit');
            Route::put('plans/{plan}', [PlanController::class, 'update'])->name('plans.update');
            Route::delete('plans/{plan}', [PlanController::class, 'destroy'])->name('plans.destroy');
        });
    });
});

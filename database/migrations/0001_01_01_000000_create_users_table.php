<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->enum('role', ['superadmin', 'admin', 'user'])->default('user');
            $table->enum('subscription_tier', ['free', 'pro'])->default('free');
            $table->timestamp('subscription_expires_at')->nullable();
            $table->enum('status', ['active', 'locked'])->default('active');
            $table->string('provider')->nullable();
            $table->string('provider_id')->nullable();
            $table->index(['provider', 'provider_id']);
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('duration_days');
            $table->decimal('price', 10, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('payment_method')->nullable();
            $table->string('transaction_code')->nullable()->unique();
            $table->enum('status', ['pending', 'success', 'failed'])->default('pending');
            $table->timestamps();
        });

        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('task_type', ['task_1', 'task_2']);
            $table->string('question_type')->nullable();
            $table->text('prompt_text');
            $table->string('image_path')->nullable();
            $table->longText('sample_essay');
            $table->decimal('band_score', 3, 1)->nullable();
            $table->decimal('tr_score', 3, 1)->nullable();
            $table->decimal('cc_score', 3, 1)->nullable();
            $table->decimal('lr_score', 3, 1)->nullable();
            $table->decimal('gra_score', 3, 1)->nullable();
            $table->boolean('is_premium')->default(false);
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->timestamps();
        });

        Schema::create('lesson_annotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->string('selected_text', 500);
            $table->unsignedInteger('start_offset')->nullable();
            $table->unsignedInteger('end_offset')->nullable();
            $table->enum('tag_type', ['vocabulary', 'grammar', 'coherence', 'logic']);
            $table->enum('access_tier', ['free', 'pro'])->default('free');
            $table->text('explanation');
            $table->timestamps();
        });

        Schema::create('lesson_vocabularies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->string('word');
            $table->string('meaning_vi');
            $table->string('meaning_en')->nullable();
            $table->text('example_sentence')->nullable();
            $table->enum('access_tier', ['free', 'pro'])->default('free');
            $table->timestamps();
            $table->unique(['lesson_id', 'word']);
        });

        Schema::create('dictation_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->integer('wpm');
            $table->decimal('accuracy', 5, 2);
            $table->timestamp('completed_at')->useCurrent();
            $table->timestamps();
            $table->index(['user_id', 'completed_at']);
        });

        Schema::create('mock_exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->longText('user_essay');
            $table->integer('word_count');
            $table->integer('time_taken_seconds');
            $table->decimal('overall_band', 3, 1)->nullable();
            $table->decimal('tr_score', 3, 1)->nullable();
            $table->decimal('cc_score', 3, 1)->nullable();
            $table->decimal('lr_score', 3, 1)->nullable();
            $table->decimal('gra_score', 3, 1)->nullable();
            $table->json('ai_feedback')->nullable();
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamps();
            $table->index(['user_id', 'submitted_at']);
        });

        Schema::create('user_vocabularies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_id')->nullable()->constrained()->nullOnDelete();
            $table->string('word');
            $table->string('meaning');
            $table->text('context_sentence')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'word']);
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration')->index();
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration')->index();
        });

        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('user_vocabularies');
        Schema::dropIfExists('mock_exams');
        Schema::dropIfExists('dictation_histories');
        Schema::dropIfExists('lesson_vocabularies');
        Schema::dropIfExists('lesson_annotations');
        Schema::dropIfExists('lessons');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('plans');
        Schema::dropIfExists('users');
    }
};

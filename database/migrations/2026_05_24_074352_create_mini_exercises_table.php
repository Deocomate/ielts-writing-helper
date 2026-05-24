<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mini_exercises', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->enum('mistake_type', ['tense', 'structure', 'vocabulary', 'punctuation']);
            $table->enum('exercise_type', ['fill_blank', 'drag_drop', 'short_answer']);
            $table->enum('difficulty_level', ['easy', 'medium', 'hard'])->default('easy');
            $table->json('question_data');
            $table->text('explanation');
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->timestamps();

            $table->index(['status', 'mistake_type', 'exercise_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mini_exercises');
    }
};

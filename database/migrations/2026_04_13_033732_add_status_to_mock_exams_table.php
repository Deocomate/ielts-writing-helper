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
        if (!Schema::hasColumn('mock_exams', 'status')) {
            Schema::table('mock_exams', function (Blueprint $table) {
                $table->enum('status', ['grading', 'completed', 'failed'])
                    ->default('completed')
                    ->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('mock_exams', 'status')) {
            Schema::table('mock_exams', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
};

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
        Schema::create('student_answers', function (Blueprint $table) {
    $table->id();

    $table->foreignId('attempt_id')
        ->constrained()->cascadeOnDelete();

    $table->foreignId('question_id')
        ->constrained()->cascadeOnDelete();

    $table->foreignId('option_id')
        ->nullable()->constrained('options')->nullOnDelete();

    $table->boolean('is_correct')->default(false);

    $table->decimal('marks_awarded', 8, 2)->default(0);

    $table->timestamps();

    $table->unique(['attempt_id', 'question_id']);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_answers');
    }
};

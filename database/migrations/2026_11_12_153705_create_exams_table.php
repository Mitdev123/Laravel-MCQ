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
        Schema::create('exams', function (Blueprint $table) {
    $table->id();

    $table->string('title');

    $table->foreignId('subject_id')
        ->nullable()->constrained()->nullOnDelete();

    $table->unsignedInteger('total_questions');

    $table->unsignedInteger('duration_minutes');

    $table->decimal('marks_per_question', 8, 2)->default(1);

    $table->decimal('negative_marks', 8, 2)->default(0);

    $table->boolean('random_questions')->default(true);

    $table->boolean('show_answers')->default(true);

    $table->dateTime('starts_at')->nullable();

    $table->dateTime('ends_at')->nullable();

    $table->string('type')->default('practice');

    $table->string('status')->default('draft');

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};

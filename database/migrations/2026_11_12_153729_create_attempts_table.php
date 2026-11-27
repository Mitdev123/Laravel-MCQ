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
        Schema::create('attempts', function (Blueprint $table) {
    $table->id();

    $table->foreignId('user_id')
        ->constrained()->cascadeOnDelete();

    $table->foreignId('exam_id')
        ->constrained()->cascadeOnDelete();

    $table->dateTime('started_at');

    $table->dateTime('expires_at');

    $table->dateTime('submitted_at')->nullable();

    $table->decimal('score', 10, 2)->default(0);

    $table->unsignedInteger('correct_answers')->default(0);

    $table->unsignedInteger('wrong_answers')->default(0);

    $table->unsignedInteger('unanswered')->default(0);

    $table->string('status')->default('in_progress');

    $table->timestamps();

    $table->index(['user_id', 'exam_id']);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attempts');
    }
};

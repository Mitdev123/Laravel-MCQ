<?php

namespace App\Services;

use App\Models\Attempt;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExamScoringService
{
    public function submit(Attempt $attempt, array $answers): Attempt
    {
        return DB::transaction(function () use ($attempt, $answers) {

            $attempt = Attempt::whereKey($attempt->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($attempt->status !== 'in_progress') {
                throw ValidationException::withMessages([
                    'exam' => 'This examination has already been submitted.'
                ]);
            }

            $exam = $attempt->exam;

            $questionIds = DB::table('attempt_questions')
    ->where('attempt_id', $attempt->id)
    ->orderBy('position')
    ->pluck('question_id')
    ->map(fn ($id) => (int) $id)
    ->all();

            $score = 0;
            $correct = 0;
            $wrong = 0;
            $unanswered = 0;

            foreach ($questionIds as $questionId) {

                $optionId = $answers[$questionId] ?? null;

                $question = $exam->questions()
                    ->where('questions.id', $questionId)
                    ->with('options')
                    ->firstOrFail();

                $option = $optionId
                    ? $question->options->firstWhere(
                        'id',
                        (int) $optionId
                    )
                    : null;

                if ($optionId && !$option) {
                    throw ValidationException::withMessages([
                        'answers' => 'An invalid answer was submitted.'
                    ]);
                }

                if (now()->gte($attempt->expires_at)) {
    $answers = [];
}

                $marks = 0;
                $isCorrect = false;

                if (!$option) {
                    $unanswered++;
                } elseif ($option->is_correct) {
                    $correct++;
                    $isCorrect = true;
                    $marks = (float) $exam->marks_per_question;
                } else {
                    $wrong++;
                    $marks = -(float) $exam->negative_marks;
                }

                $score += $marks;

                $attempt->answers()->updateOrCreate(
                    ['question_id' => $questionId],
                    [
                        'option_id' => $option?->id,
                        'is_correct' => $isCorrect,
                        'marks_awarded' => $marks
                    ]
                );
            }

            $attempt->update([
                'score' => $score,
                'correct_answers' => $correct,
                'wrong_answers' => $wrong,
                'unanswered' => $unanswered,
                'submitted_at' => now(),
                'status' => 'submitted'
            ]);

            return $attempt->fresh();
        });
    }
}
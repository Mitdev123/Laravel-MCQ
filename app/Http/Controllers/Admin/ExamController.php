<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\AttemptAnswer;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExamController extends Controller
{
    public function index()
    {
        $exams = Exam::with('subject')
            ->where('status', true)
            ->where('is_practice', false)
            ->latest()->paginate(12);

        return view('student.exams.index', compact('exams'));
    }

    public function start(Exam $exam)
    {
        abort_unless($exam->status, 404);

        $now = now();

        if ($exam->starts_at && $now->lt($exam->starts_at)) {
            abort(403, 'Exam has not started.');
        }

        if ($exam->ends_at && $now->gt($exam->ends_at)) {
            abort(403, 'Exam has ended.');
        }

        $existing = ExamAttempt::where('exam_id', $exam->id)
            ->where('user_id', auth()->id())
            ->where('status', 'in_progress')
            ->first();

        if ($existing) {
            return redirect()->route(
                'student.exam.take', $existing
            );
        }

        $questions = Question::with('options')
            ->where('subject_id', $exam->subject_id)
            ->where('status', true);

        if ($exam->random_questions) {
            $questions->inRandomOrder();
        } else {
            $questions->orderBy('id');
        }

        $selected = $questions
            ->limit($exam->total_questions)
            ->get();

        abort_if($selected->isEmpty(), 422, 'No questions available.');

        $attempt = DB::transaction(function () use ($exam, $selected) {
            $attempt = ExamAttempt::create([
                'exam_id' => $exam->id,
                'user_id' => auth()->id(),
                'type' => 'online',
                'started_at' => now(),
                'expires_at' => now()->addMinutes($exam->duration),
                'total_marks' => $selected->sum('marks'),
            ]);

            foreach ($selected as $question) {
                AttemptAnswer::create([
                    'exam_attempt_id' => $attempt->id,
                    'question_id' => $question->id,
                ]);
            }

            return $attempt;
        });

        return redirect()->route('student.exam.take', $attempt);
    }

    public function take(ExamAttempt $attempt)
    {
        $this->authorizeAttempt($attempt);

        if ($attempt->status !== 'in_progress') {
            return redirect()->route(
                'student.result.show', $attempt
            );
        }

        if (now()->gte($attempt->expires_at)) {
            return $this->submitAttempt($attempt);
        }

        $attempt->load('exam');

        $questions = Question::with('options')
            ->whereIn(
                'id',
                $attempt->answers()->pluck('question_id')
            )->get();

        $ordered = $attempt->answers()
            ->pluck('question_id')
            ->flip();

        $questions = $questions->sortBy(
            fn($q) => $ordered[$q->id]
        )->values();

        return view('student.exams.take', compact(
            'attempt', 'questions'
        ));
    }

    public function saveAnswer(Request $request, ExamAttempt $attempt)
    {
        $this->authorizeAttempt($attempt);

        if ($attempt->status !== 'in_progress' ||
            now()->gte($attempt->expires_at)) {
            return response()->json([
                'message' => 'Exam expired or submitted.'
            ], 403);
        }

        $data = $request->validate([
            'question_id' => 'required|integer',
            'option_id' => 'nullable|integer',
        ]);

        $answer = $attempt->answers()
            ->where('question_id', $data['question_id'])
            ->firstOrFail();

        if ($data['option_id'] !== null) {
            $validOption = $answer->question
                ->options()
                ->whereKey($data['option_id'])
                ->exists();

            abort_unless($validOption, 422);
        }

        $answer->update([
            'selected_option_id' => $data['option_id'],
        ]);

        return response()->json(['saved' => true]);
    }

    public function submit(ExamAttempt $attempt)
    {
        $this->authorizeAttempt($attempt);

        return $this->submitAttempt($attempt);
    }

    private function submitAttempt(ExamAttempt $attempt)
    {
        if ($attempt->status !== 'in_progress') {
            return redirect()->route(
                'student.result.show', $attempt
            );
        }

        DB::transaction(function () use ($attempt) {
            $attempt = ExamAttempt::whereKey($attempt->id)
                ->lockForUpdate()->firstOrFail();

            if ($attempt->status !== 'in_progress') {
                return;
            }

            $answers = $attempt->answers()
                ->with(['question.options'])
                ->get();

            $score = 0;
            $correct = 0;
            $wrong = 0;
            $unanswered = 0;
            $total = 0;

            foreach ($answers as $answer) {
                $question = $answer->question;

                $marks = (float) $question->marks;
                $negative = (float) $question->negative_marks;

                $total += $marks;

                if (!$answer->selected_option_id) {
                    $unanswered++;
                    $awarded = 0;
                    $isCorrect = false;
                } else {
                    $option = $question->options->firstWhere(
                        'id', $answer->selected_option_id
                    );

                    $isCorrect = $option && $option->is_correct;

                    if ($isCorrect) {
                        $correct++;
                        $awarded = $marks;
                    } else {
                        $wrong++;
                        $awarded = -$negative;
                    }
                }

                $score += $awarded;

                $answer->update([
                    'is_correct' => $isCorrect,
                    'awarded_marks' => $awarded,
                ]);
            }

            $attempt->update([
                'score' => $score,
                'total_marks' => $total,
                'correct_answers' => $correct,
                'wrong_answers' => $wrong,
                'unanswered' => $unanswered,
                'submitted_at' => now(),
                'status' => 'submitted',
            ]);
        });

        return redirect()->route(
            'student.result.show', $attempt
        );
    }

    private function authorizeAttempt(ExamAttempt $attempt): void
    {
        abort_unless(
            $attempt->user_id === auth()->id(),
            403
        );
    }
}
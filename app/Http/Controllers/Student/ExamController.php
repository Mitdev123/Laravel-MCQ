<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Question;
use App\Models\Attempt;
use App\Models\AttemptAnswer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ExamController extends Controller
{
    public function index()
    {
        $exams = Exam::where('status', true)
            ->with('subject')
            ->latest()
            ->paginate(12);

        return view('student.exams.index', compact('exams'));
    }

    public function start(Exam $exam)
    {
        abort_unless($exam->status, 404);

        $attempt = DB::transaction(function () use ($exam) {

            $questions = Question::where(
                'subject_id',
                $exam->subject_id
            )->where('status', true);

            if ($exam->random_questions) {
                $questions->inRandomOrder();
            }

            $selected = $questions
                ->limit($exam->total_questions)
                ->get();

            if ($selected->count() < $exam->total_questions) {
                abort(422, 'Not enough questions available.');
            }

            $attempt = Attempt::create([
                'user_id' => auth()->id(),
                'exam_id' => $exam->id,
                'type' => 'exam',
                'total_marks' => $exam->total_questions *
                    $exam->marks_per_question,
                'started_at' => now(),
                'ends_at' => now()->addMinutes($exam->duration),
                'status' => 'in_progress',
            ]);

            foreach ($selected as $question) {
                AttemptAnswer::create([
                    'attempt_id' => $attempt->id,
                    'question_id' => $question->id,
                ]);
            }

            return $attempt;
        });

        return redirect()->route(
            'student.exam.show',
            $attempt
        );
    }

    public function show(Attempt $attempt)
    {
        abort_unless($attempt->user_id === auth()->id(), 403);

        if ($attempt->status !== 'in_progress') {
            return redirect()->route(
                'student.results.show',
                $attempt
            );
        }

        if (now()->greaterThanOrEqualTo($attempt->ends_at)) {
            $this->finish($attempt);

            return redirect()->route(
                'student.results.show',
                $attempt
            );
        }

        $attempt->load('answers.question');

        return view(
            'student.exams.show',
            compact('attempt')
        );
    }

    public function saveAnswer(Request $request, Attempt $attempt)
    {
        abort_unless($attempt->user_id === auth()->id(), 403);

        if ($attempt->status !== 'in_progress' ||
            now()->greaterThanOrEqualTo($attempt->ends_at)) {
            return response()->json([
                'message' => 'Examination has ended.',
            ], 422);
        }

        $data = $request->validate([
            'question_id' => 'required|integer',
            'answer' => 'nullable|in:A,B,C,D',
        ]);

        $answer = AttemptAnswer::where(
            'attempt_id',
            $attempt->id
        )->where(
            'question_id',
            $data['question_id']
        )->firstOrFail();

        $answer->update([
            'selected_answer' => $data['answer'],
        ]);

        return response()->json([
            'success' => true,
        ]);
    }

    public function submit(Attempt $attempt)
    {
        abort_unless($attempt->user_id === auth()->id(), 403);

        $this->finish($attempt);

        return redirect()->route(
            'student.results.show',
            $attempt
        );
    }

    private function finish(Attempt $attempt): void
    {
        DB::transaction(function () use ($attempt) {

            $attempt = Attempt::whereKey($attempt->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($attempt->status !== 'in_progress') {
                return;
            }

            $correct = 0;
            $wrong = 0;
            $unanswered = 0;
            $score = 0;

            $attempt->load('answers.question');

            $exam = $attempt->exam;

            foreach ($attempt->answers as $answer) {

                $question = $answer->question;

                if (!$answer->selected_answer) {
                    $unanswered++;
                    $awarded = 0;
                    $isCorrect = false;
                } elseif (
                    strtoupper($answer->selected_answer) ===
                    strtoupper($question->correct_answer)
                ) {
                    $correct++;
                    $isCorrect = true;
                    $awarded = $exam->marks_per_question;
                } else {
                    $wrong++;
                    $isCorrect = false;
                    $awarded = -$exam->negative_marks;
                }

                $answer->update([
                    'is_correct' => $isCorrect,
                    'awarded_marks' => $awarded,
                ]);

                $score += $awarded;
            }

            $attempt->update([
                'correct_answers' => $correct,
                'wrong_answers' => $wrong,
                'unanswered' => $unanswered,
                'obtained_marks' => $score,
                'submitted_at' => now(),
                'status' => 'submitted',
            ]);
        });
    }
}
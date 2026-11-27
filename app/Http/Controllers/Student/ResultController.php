<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ExamAttempt;

class ResultController extends Controller
{
    public function index()
    {
        $results = ExamAttempt::with('exam')
            ->where('user_id', auth()->id())
            ->where('status', 'submitted')
            ->latest()->paginate(15);

        return view('student.results.index', compact('results'));
    }

    public function show(ExamAttempt $attempt)
    {
        abort_unless(
            $attempt->user_id === auth()->id(),
            403
        );

        abort_unless($attempt->status === 'submitted', 403);

        $attempt->load([
            'exam',
            'answers.question.options',
            'answers.selectedOption',
        ]);

        $percentage = $attempt->total_marks > 0
            ? ($attempt->score / $attempt->total_marks) * 100
            : 0;

        $passed = $percentage >= $attempt->exam->pass_percentage;

        return view('student.results.show', compact(
            'attempt', 'percentage', 'passed'
        ));
    }
}
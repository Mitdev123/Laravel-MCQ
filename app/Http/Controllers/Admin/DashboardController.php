<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Question;
use App\Models\Exam;
use App\Models\ExamAttempt;

class DashboardController extends Controller
{
    public function index()
    {
        $students = User::where('role', 'student')->count();

        $questions = Question::count();

        $totalExams = Exam::count();

        $totalAttempts = ExamAttempt::count();

        // Collections for foreach loops
        $exams = Exam::latest()->take(10)->get();

        $attempts = ExamAttempt::with(['user', 'exam'])
            ->latest()
            ->take(10)
            ->get();

        return view('admin.dashboard', compact(
            'students',
            'questions',
            'totalExams',
            'totalAttempts',
            'exams',
            'attempts'
        ));
    }
}

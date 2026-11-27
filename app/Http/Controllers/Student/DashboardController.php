<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Attempt;
use App\Models\Exam;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $exams = Exam::where('status', true)
            ->with('subject')
            ->latest()
            ->take(5)
            ->get();

        $attempts = Attempt::where('user_id', $user->id)
            ->latest()
            ->take(10)
            ->get();

        $totalAttempts = Attempt::where('user_id', $user->id)
            ->where('status', 'submitted')
            ->count();

        return view('student.dashboard', compact(
            'exams',
            'attempts',
            'totalAttempts'
        ));
    }
}
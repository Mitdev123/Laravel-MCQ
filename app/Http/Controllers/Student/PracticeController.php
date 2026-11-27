<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use Illuminate\Http\Request;

class PracticeController extends Controller
{
    public function index()
    {
        $exams = Exam::with('subject')
            ->where('status', true)
            ->where('is_practice', true)
            ->latest()->paginate(12);

        return view('student.practice.index', compact('exams'));
    }

    public function start(Exam $exam, ExamController $engine)
    {
        abort_unless($exam->is_practice && $exam->status, 404);

        return $engine->start($exam);
    }
}
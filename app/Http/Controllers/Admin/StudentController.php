<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Student;

class StudentController extends Controller
{
    public function index()
{
    $students = Student::all();

    return view('admin.students.index', compact('students'));
}

    public function toggleStatus(User $user)
    {
        abort_unless($user->role === 'student', 404);

        $user->update([
            'is_active' => !$user->is_active
        ]);

        return back()->with('success', 'Student status updated.');
    }
}
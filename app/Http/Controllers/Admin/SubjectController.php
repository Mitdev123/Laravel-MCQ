<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SubjectController extends Controller
{
    public function index()
    {
        $subjects = Subject::with('category')
            ->latest()
            ->paginate(15);

        return view('admin.subjects.index', compact('subjects'));
    }

    public function create()
    {
        $categories = Category::all();

        return view('admin.subjects.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:subjects,name',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
        ]);

        $data['slug'] = Str::slug($data['name']);

        Subject::create($data);

        return redirect()->route('admin.subjects.index')
            ->with('success', 'Subject created successfully.');
    }

    public function edit(Subject $subject)
    {
        $categories = Category::all();

        return view('admin.subjects.edit', compact('subject', 'categories'));
    }

    public function update(Request $request, Subject $subject)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:subjects,name,' . $subject->id,
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
        ]);

        $data['slug'] = Str::slug($data['name']);

        $subject->update($data);

        return redirect()->route('admin.subjects.index')
            ->with('success', 'Subject updated successfully.');
    }

    public function destroy(Subject $subject)
    {
        if ($subject->questions()->exists() || $subject->exams()->exists()) {
            return back()->withErrors([
                'subject' => 'Cannot delete a subject containing questions or exams.'
            ]);
        }

        $subject->delete();

        return back()->with('success', 'Subject deleted.');
    }
}

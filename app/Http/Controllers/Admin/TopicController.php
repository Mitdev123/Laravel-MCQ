<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Topic;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TopicController extends Controller
{
    public function index()
    {
        $topics = Topic::with('subject')
            ->latest()
            ->paginate(15);

        return view('admin.topics.index', compact('topics'));
    }

    public function create()
    {
        $subjects = Subject::where('is_active', true)->get();

        return view('admin.topics.create', compact('subjects'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'name' => 'required|string|max:255',
        ]);

        Topic::create($data);

        return redirect()->route('admin.topics.index')
            ->with('success', 'Topic created successfully.');
    }

    public function show(Topic $topic)
    {
        return view('admin.topics.show', compact('topic'));
    }

    public function edit(Topic $topic)
    {
        $subjects = Subject::all();

        return view('admin.topics.edit', compact(
            'topic',
            'subjects'
        ));
    }

    public function update(Request $request, Topic $topic)
    {
        $data = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'name' => 'required|string|max:255',
        ]);

        $topic->update($data);

        return redirect()->route('admin.topics.index')
            ->with('success', 'Topic updated successfully.');
    }

    public function destroy(Topic $topic)
    {
        $topic->delete();

        return redirect()->route('admin.topics.index')
            ->with('success', 'Topic deleted successfully.');
    }
}

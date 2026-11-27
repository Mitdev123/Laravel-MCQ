<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Subject;
use App\Models\Category;
use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class QuestionController extends Controller
{
    public function index(Request $request)
    {
        $questions = Question::with(['subject', 'topic', 'options'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('question_text', 'like', '%' .
                    $request->search . '%');
            })
            ->when($request->filled('subject_id'), function ($query) use ($request) {
                $query->where('subject_id', $request->subject_id);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $subjects = Subject::orderBy('name')->get();

        return view('admin.questions.index', compact(
            'questions',
            'subjects'
        ));
    }

    

public function create()
{
    $subjects = Subject::orderBy('name')->get();
    $categories = Category::orderBy('name')->get();

    return view('admin.questions.create', compact(
        'subjects',
        'categories'
    ));
}

   

public function store(Request $request)
{
    $data = $request->validate([
        'subject_id' => 'required|exists:subjects,id',
        'question_text' => 'required|string|max:20000',
        'explanation' => 'nullable|string',
        'difficulty' => 'required|in:easy,medium,hard',
        'is_active' => 'required|boolean',

        'options' => 'required|array|min:2|max:10',
        'options.*.option_label' => 'required|string|max:5',
        'options.*.option_text' => 'required|string|max:5000',
        'options.*.is_correct' => 'nullable|boolean',
    ]);

    $correctCount = collect($data['options'])
        ->filter(fn ($option) => !empty($option['is_correct']))
        ->count();

    if ($correctCount < 1) {
        return back()->withErrors([
            'options' => 'Please select at least one correct answer.'
        ])->withInput();
    }

    DB::transaction(function () use ($data) {

        $question = Question::create([
            'subject_id' => $data['subject_id'],
            'question' => $data['question_text'],
            'explanation' => $data['explanation'] ?? null,
            'difficulty' => $data['difficulty'],
            'status' => $data['is_active'],
        ]);

        $this->saveOptions($question, $data['options']);
    });

    return redirect()
        ->route('admin.questions.index')
        ->with('success', 'Question created successfully!');
}


    public function show(Question $question)
    {
        $question->load(['subject', 'topic', 'options']);

        return view('admin.questions.show', compact('question'));
    }

    public function edit(Question $question)
    {
        $question->load('options');

        $subjects = Subject::orderBy('name')->get();

        $topics = Topic::where('subject_id', $question->subject_id)
            ->where('is_active', true)
            ->get();

        return view('admin.questions.edit', compact(
            'question',
            'subjects',
            'topics'
        ));
    }

    public function update(Request $request, Question $question)
    {
        $data = $this->validateQuestion($request);

        DB::transaction(function () use ($data, $question) {
            $question->update([
                'subject_id' => $data['subject_id'],
                'topic_id' => $data['topic_id'] ?? null,
                'question_text' => $data['question_text'],
                'explanation' => $data['explanation'] ?? null,
                'marks' => $data['marks'],
                'negative_marks' => $data['negative_marks'],
                'question_type' => $data['question_type'],
                'is_active' => $data['is_active'],
            ]);

            // Synchronize options without leaving old options behind.
            $this->saveOptions($question, $data['options']);
        });

        return redirect()
            ->route('admin.questions.index')
            ->with('success', 'Question updated successfully.');
    }

    public function destroy(Question $question)
    {
        $question->delete();

        return redirect()
            ->route('admin.questions.index')
            ->with('success', 'Question deleted successfully.');
    }

    private function validateQuestion(Request $request): array
    {
        $data = $request->validate([
            'subject_id' => [
                'required',
                'integer',
                'exists:subjects,id',
            ],

            'topic_id' => [
                'nullable',
                'integer',
                Rule::exists('topics', 'id')
                    ->where(fn ($query) => $query->where(
                        'subject_id',
                        $request->input('subject_id')
                    )),
            ],

            'question_text' => [
                'required',
                'string',
                'max:20000',
            ],

            'explanation' => [
                'nullable',
                'string',
                'max:20000',
            ],

            'marks' => [
                'required',
                'numeric',
                'min:0.01',
                'max:1000',
            ],

            'negative_marks' => [
                'required',
                'numeric',
                'min:0',
                'max:1000',
            ],

            'question_type' => [
                'required',
                Rule::in(['single', 'multiple']),
            ],

            'is_active' => [
                'required',
                'boolean',
            ],

            'options' => [
                'required',
                'array',
                'min:2',
                'max:10',
            ],

            'options.*.option_label' => [
                'required',
                'string',
                'max:5',
                'distinct',
            ],

            'options.*.option_text' => [
                'required',
                'string',
                'max:5000',
            ],

            'options.*.is_correct' => [
                'nullable',
                'boolean',
            ],
        ]);

        $correctCount = collect($data['options'])
            ->filter(fn ($option) =>
                (bool) ($option['is_correct'] ?? false)
            )
            ->count();

        if (
            $data['question_type'] === 'single' &&
            $correctCount !== 1
        ) {
            throw ValidationException::withMessages([
                'options' => 'A single-answer question must have exactly one correct option.',
            ]);
        }

        if (
            $data['question_type'] === 'multiple' &&
            $correctCount < 1
        ) {
            throw ValidationException::withMessages([
                'options' => 'Select at least one correct option.',
            ]);
        }

        return $data;
    }

    private function saveOptions(Question $question, array $options): void
    {
        $question->options()->delete();

        foreach (array_values($options) as $index => $option) {
            $question->options()->create([
                'option_label' => $option['option_label'],
                'option_text' => $option['option_text'],
                'is_correct' => (bool) ($option['is_correct'] ?? false),
                'sort_order' => $index,
            ]);
        }
    }
}

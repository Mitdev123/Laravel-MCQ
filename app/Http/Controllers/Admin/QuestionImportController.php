<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\QuestionsImport;
use App\Models\Question;
use App\Models\QuestionImportBatch;
use App\Models\QuestionOption;
use App\Models\Subject;
use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class QuestionImportController extends Controller
{
    public function index()
    {
        $batches = QuestionImportBatch::where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('admin.questions.import', compact('batches'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => [
                'required',
                'file',
                'mimes:xlsx,xls,csv,txt',
                'max:10240',
            ],
        ]);

        try {
            $import = new QuestionsImport();

            Excel::import($import, $request->file('file'));

            $rows = $import->rows ?? collect();

            if ($rows->isEmpty()) {
                return back()->withErrors([
                    'file' => 'The uploaded file contains no question records.',
                ]);
            }

            if ($rows->count() > 1000) {
                return back()->withErrors([
                    'file' => 'Maximum 1000 questions can be imported per batch.',
                ]);
            }

            $preview = [];
            $seen = [];

            foreach ($rows as $index => $row) {

                $data = $row->toArray();

                $rowNumber = $index + 2;

                $options = [];

                foreach ($data as $key => $value) {

                    if (preg_match('/^option_([a-z])$/', $key, $matches)) {

                        $label = strtoupper($matches[1]);

                        if (trim((string) $value) !== '') {
                            $options[$label] = trim((string) $value);
                        }
                    }
                }

                ksort($options);

                $correctAnswers = collect(
                    explode(',', strtoupper(
                        trim((string) ($data['correct_answer'] ?? ''))
                    ))
                )
                    ->map(fn ($answer) => trim($answer))
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                $type = strtolower(
                    trim((string) ($data['question_type'] ?? 'single'))
                );

                $subject = Subject::whereRaw(
                    'LOWER(name) = ?',
                    [strtolower(trim((string) ($data['subject'] ?? '')))]
                )->first();

                $topic = null;
                $errors = [];

                if ($subject && !empty($data['topic'])) {
                    $topic = Topic::where('subject_id', $subject->id)
                        ->whereRaw(
                            'LOWER(name) = ?',
                            [strtolower(trim((string) $data['topic']))]
                        )
                        ->first();
                }

                $normalizedQuestion = mb_strtolower(
                    trim((string) ($data['question'] ?? ''))
                );

                $validator = Validator::make([
                    'subject' => $data['subject'] ?? null,
                    'question' => $data['question'] ?? null,
                    'marks' => $data['marks'] ?? 1,
                    'negative_marks' => $data['negative_marks'] ?? 0,
                    'question_type' => $type,
                ], [
                    'subject' => 'required|string',
                    'question' => 'required|string|max:20000',
                    'marks' => 'required|numeric|gt:0|max:1000',
                    'negative_marks' => 'required|numeric|min:0|max:1000',
                    'question_type' => 'required|in:single,multiple',
                ]);

                if ($validator->fails()) {
                    $errors = array_merge(
                        $errors,
                        $validator->errors()->all()
                    );
                }

                if (!$subject) {
                    $errors[] = 'Subject does not exist.';
                }

                if (
                    !empty($data['topic']) &&
                    $subject &&
                    !$topic
                ) {
                    $errors[] = 'Topic does not exist under the selected subject.';
                }

                if (count($options) < 2) {
                    $errors[] = 'At least two options are required.';
                }

                if (!empty(array_diff($correctAnswers, array_keys($options)))) {
                    $errors[] = 'Correct answer contains an invalid option label.';
                }

                if ($type === 'single' && count($correctAnswers) !== 1) {
                    $errors[] = 'Single questions must have exactly one correct answer.';
                }

                if ($type === 'multiple' && count($correctAnswers) < 1) {
                    $errors[] = 'Multiple questions need at least one correct answer.';
                }

                if (!in_array($type, ['single', 'multiple'], true)) {
                    $errors[] = 'Invalid question type.';
                }

                $duplicateKey = ($subject?->id ?? 'unknown')
                    . '|' . $normalizedQuestion;

                $duplicate = false;

                if ($subject && $normalizedQuestion !== '') {

                    $duplicate = Question::where(
                        'subject_id',
                        $subject->id
                    )->whereRaw(
                        'LOWER(TRIM(question_text)) = ?',
                        [$normalizedQuestion]
                    )->exists();

                    if (isset($seen[$duplicateKey])) {
                        $duplicate = true;
                    }

                    $seen[$duplicateKey] = true;
                }

                if ($duplicate) {
                    $errors[] = 'Duplicate question found.';
                }

                $preview[] = [
                    'row_number' => $rowNumber,
                    'subject_id' => $subject?->id,
                    'topic_id' => $topic?->id,
                    'question_text' => trim(
                        (string) ($data['question'] ?? '')
                    ),
                    'explanation' => $data['explanation'] ?? null,
                    'marks' => $data['marks'] ?? 1,
                    'negative_marks' => $data['negative_marks'] ?? 0,
                    'question_type' => $type,
                    'options' => $options,
                    'correct_answers' => $correctAnswers,
                    'valid' => empty($errors),
                    'duplicate' => $duplicate,
                    'errors' => array_values(array_unique($errors)),
                ];
            }

            $valid = collect($preview)->where('valid', true)->count();

            $invalid = collect($preview)->where('valid', false)->count();

            $duplicates = collect($preview)
                ->where('duplicate', true)
                ->count();

            $batch = QuestionImportBatch::create([
                'user_id' => auth()->id(),
                'file_name' => $request->file('file')->getClientOriginalName(),
                'preview_data' => $preview,
                'total_rows' => count($preview),
                'valid_rows' => $valid,
                'invalid_rows' => $invalid,
                'duplicate_rows' => $duplicates,
                'status' => 'pending',
                'expires_at' => now()->addHours(24),
            ]);

            return redirect()
                ->route('admin.questions.import.preview', $batch)
                ->with('success', 'File uploaded and validated. Review the preview before importing.');

        } catch (Throwable $e) {

            report($e);

            return back()->withErrors([
                'file' => 'Unable to read the spreadsheet. Check its format and column headings.',
            ]);
        }
    }

    public function preview(QuestionImportBatch $batch)
    {
        abort_unless($batch->user_id === auth()->id(), 403);

        if (
            $batch->status !== 'pending' ||
            ($batch->expires_at && $batch->expires_at->isPast())
        ) {
            abort(410, 'This import batch is no longer available.');
        }

        return view('admin.questions.import-preview', compact('batch'));
    }

    public function confirm(QuestionImportBatch $batch)
    {
        abort_unless($batch->user_id === auth()->id(), 403);

        if (
            $batch->status !== 'pending' ||
            ($batch->expires_at && $batch->expires_at->isPast())
        ) {
            abort(410, 'This import batch is no longer available.');
        }

        $rows = collect($batch->preview_data);

        if (!$rows->contains(fn ($row) => $row['valid'] === true)) {
            return back()->withErrors([
                'import' => 'There are no valid questions to import.',
            ]);
        }

        try {

            DB::transaction(function () use ($batch, $rows) {

                // Lock the batch to prevent simultaneous confirmations.
                $lockedBatch = QuestionImportBatch::whereKey($batch->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($lockedBatch->status !== 'pending') {
                    throw ValidationException::withMessages([
                        'import' => 'This import has already been processed.',
                    ]);
                }

                if (
                    $lockedBatch->expires_at &&
                    $lockedBatch->expires_at->isPast()
                ) {
                    throw ValidationException::withMessages([
                        'import' => 'This import batch has expired.',
                    ]);
                }

                foreach ($rows as $row) {

                    if (!$row['valid']) {
                        continue;
                    }

                    $subject = Subject::find($row['subject_id']);

                    if (!$subject) {
                        throw ValidationException::withMessages([
                            'import' => 'A selected subject no longer exists.',
                        ]);
                    }

                    if (
                        !empty($row['topic_id']) &&
                        !Topic::where('id', $row['topic_id'])
                            ->where('subject_id', $subject->id)
                            ->exists()
                    ) {
                        throw ValidationException::withMessages([
                            'import' => 'A selected topic is no longer valid.',
                        ]);
                    }

                    // Check duplicates again before inserting.
                    $exists = Question::where(
                        'subject_id',
                        $subject->id
                    )->whereRaw(
                        'LOWER(TRIM(question_text)) = ?',
                        [mb_strtolower(trim($row['question_text']))]
                    )->exists();

                    if ($exists) {
                        throw ValidationException::withMessages([
                            'import' => 'A question became a duplicate after preview. Please upload and preview the file again.',
                        ]);
                    }

                    $question = Question::create([
                        'subject_id' => $subject->id,
                        'topic_id' => $row['topic_id'],
                        'question_text' => $row['question_text'],
                        'explanation' => $row['explanation'],
                        'marks' => $row['marks'],
                        'negative_marks' => $row['negative_marks'],
                        'question_type' => $row['question_type'],
                        'is_active' => true,
                    ]);

                    foreach ($row['options'] as $label => $optionText) {

                        $question->options()->create([
                            'option_label' => $label,
                            'option_text' => $optionText,
                            'is_correct' => in_array(
                                $label,
                                $row['correct_answers'],
                                true
                            ),
                            'sort_order' => ord($label) - ord('A'),
                        ]);
                    }
                }

                $lockedBatch->update([
                    'status' => 'completed',
                ]);
            });

            return redirect()
                ->route('admin.questions.import')
                ->with('success', 'Questions imported successfully.');

        } catch (Throwable $e) {

            report($e);

            if ($e instanceof ValidationException) {
                throw $e;
            }

            return back()->withErrors([
                'import' => 'Import failed. No questions were committed. Please try again.',
            ]);
        }
    }

    public function cancel(QuestionImportBatch $batch)
    {
        abort_unless($batch->user_id === auth()->id(), 403);

        if ($batch->status === 'pending') {
            $batch->update([
                'status' => 'cancelled',
                'preview_data' => [],
            ]);
        }

        return redirect()
            ->route('admin.questions.import')
            ->with('success', 'Import batch cancelled.');
    }

    public function template()
{
    $headers = [
        'subject',
        'topic',
        'question',
        'option_a',
        'option_b',
        'option_c',
        'option_d',
        'correct_answer',
        'explanation',
        'marks',
        'negative_marks',
        'question_type',
    ];

    return response()->streamDownload(function () use ($headers) {

        $output = fopen('php://output', 'w');

        // Excel-compatible UTF-8 CSV.
        fwrite($output, "\xEF\xBB\xBF");

        fputcsv($output, $headers);

        fputcsv($output, [
            'Mathematics',
            'Algebra',
            'What is 2 + 2?',
            '3',
            '4',
            '5',
            '6',
            'B',
            'Basic addition',
            '1',
            '0.25',
            'single',
        ]);

        fclose($output);

    }, 'mcq-question-import-template.csv', [
        'Content-Type' => 'text/csv; charset=UTF-8',
    ]);
}
}

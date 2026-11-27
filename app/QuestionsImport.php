<?php

namespace App\Imports;

use App\Models\Question;
use App\Models\Subject;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

class QuestionsImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {

            if (empty($row['question']) ||
                empty($row['subject'])) {
                continue;
            }

            $subject = Subject::where(
                'name',
                trim($row['subject'])
            )->first();

            if (!$subject) {
                continue;
            }

            $options = collect([
                $row['option_a'] ?? null,
                $row['option_b'] ?? null,
                $row['option_c'] ?? null,
                $row['option_d'] ?? null,
            ])->filter(fn ($value) =>
                $value !== null && trim((string) $value) !== ''
            )->values();

            if ($options->count() < 2) {
                continue;
            }

            $correct = strtoupper(trim(
                $row['correct_answer'] ?? ''
            ));

            $correctIndex = match ($correct) {
                'A' => 0,
                'B' => 1,
                'C' => 2,
                'D' => 3,
                default => -1
            };

            if ($correctIndex < 0 ||
                !$options->has($correctIndex)) {
                continue;
            }

            DB::transaction(function () use (
                $row,
                $subject,
                $options,
                $correctIndex
            ) {
                $question = Question::create([
                    'subject_id' => $subject->id,
                    'question' => $row['question'],
                    'explanation' => $row['explanation'] ?? null,
                    'difficulty' => $row['difficulty'] ?? 'medium',
                    'status' => true
                ]);

                foreach ($options as $index => $option) {
                    $question->options()->create([
                        'option_text' => $option,
                        'is_correct' => $index === $correctIndex
                    ]);
                }
            });
        }
    }
}
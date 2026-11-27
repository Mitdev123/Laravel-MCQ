<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttemptAnswer extends Model
{
    protected $fillable = [
        'exam_attempt_id',
        'question_id',
        'question_option_id',
        'question_snapshot',
        'options_snapshot',
        'is_correct',
        'marks_awarded',
    ];

    protected function casts(): array
    {
        return [
            'question_snapshot' => 'array',
            'options_snapshot' => 'array',
            'is_correct' => 'boolean',
        ];
    }

    public function attempt()
    {
        return $this->belongsTo(
            ExamAttempt::class,
            'exam_attempt_id'
        );
    }
}

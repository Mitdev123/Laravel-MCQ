<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    protected $fillable = [
        'title',
        'subject_id',
        'total_questions',
        'duration_minutes',
        'marks_per_question',
        'negative_marks',
        'random_questions',
        'show_answers',
        'starts_at',
        'ends_at',
        'type',
        'status'
    ];

    protected $casts = [
        'random_questions' => 'boolean',
        'show_answers' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function questions()
    {
        return $this->belongsToMany(
            Question::class,
            'exam_questions'
        )->withPivot('position');
    }

    public function attempts()
    {
        return $this->hasMany(Attempt::class);
    }
}
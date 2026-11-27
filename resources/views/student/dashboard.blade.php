@extends('layouts.app')

@section('title', 'Student Dashboard')
@section('heading', 'Student Dashboard')

@section('content')

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card stat-card p-4">
            <p class="text-muted">Available Exams</p>
            <h2>{{ $examCount }}</h2>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card stat-card p-4">
            <p class="text-muted">Completed Attempts</p>
            <h2>{{ $attemptCount }}</h2>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm p-3">
    <h5>Available Examinations</h5>

    @foreach($availableExams as $exam)
        <div class="border rounded p-3 mb-2">
            <h6>{{ $exam->title }}</h6>

            <p class="text-muted mb-2">
                {{ $exam->subject->name }} |
                {{ $exam->duration }} minutes |
                {{ $exam->total_questions }} questions
            </p>

            <a href="{{ route('student.exams.start', $exam) }}"
               class="btn btn-primary btn-sm">
                Start Examination
            </a>
        </div>
    @endforeach
</div>

@endsection
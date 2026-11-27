@extends('layouts.app')

@section('content')
<div class="container py-4">

    <h2>Student Dashboard</h2>

    <h4 class="mt-4">Available Examinations</h4>
    <div class="row g-3">

    <div class="col-md-3">
        <div class="card p-3">
            <h6>Total Students</h6>
            <h2>{{ $students }}</h2>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card p-3">
            <h6>Total Questions</h6>
            <h2>{{ $questions }}</h2>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card p-3">
            <h6>Total Exams</h6>
            <h2>{{ $totalExams }}</h2>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card p-3">
            <h6>Total Attempts</h6>
            <h2>{{ $totalAttempts }}</h2>
        </div>
    </div>

    <h4 class="mt-4">Recent Student Attempts</h4>

<table class="table table-bordered bg-white">
    <thead>
        <tr>
            <th>Student</th>
            <th>Exam</th>
            <th>Score</th>
            <th>Status</th>
        </tr>
    </thead>

    <tbody>
        @forelse($attempts as $attempt)
            <tr>
                <td>{{ $attempt->user->name ?? 'Unknown' }}</td>
                <td>{{ $attempt->exam->title ?? 'Unknown' }}</td>
                <td>{{ $attempt->score }}</td>
                <td>{{ $attempt->status }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4">No attempts found.</td>
            </tr>
        @endforelse
    </tbody>
</table>

</div>

    <div class="row">
        @foreach($exams as $exam)
            <div class="col-md-4 mb-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5>{{ $exam->title }}</h5>

                        <p>{{ $exam->description }}</p>

                        <p>
                            Type: {{ ucfirst($exam->type) }}<br>
                            Duration: {{ $exam->duration_minutes }} minutes<br>
                            Questions: {{ $exam->total_questions }}
                        </p>

                        <form method="POST"
                            action="{{ route('student.exam.start', $exam) }}">
                            @csrf
                            <button class="btn btn-primary">
                                Start Examination
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <h4 class="mt-4">Previous Attempts</h4>

    <table class="table table-bordered bg-white">
        <thead>
            <tr>
                <th>Exam</th>
                <th>Score</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($attempts as $attempt)
                <tr>
                    <td>{{ $attempt->exam->title }}</td>
                    <td>{{ $attempt->score }}</td>
                    <td>{{ $attempt->status }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</div>
@endsection
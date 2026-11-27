@extends('layouts.app')

@section('heading', 'Available Examinations')

@section('content')

<div class="row g-3">

@foreach($exams as $exam)
    <div class="col-md-6">
        <div class="card border-0 shadow-sm p-3">
            <h5>{{ $exam->title }}</h5>

            <p>{{ $exam->description }}</p>

            <p class="text-muted">
                {{ $exam->subject->name }} |
                {{ ucfirst($exam->type) }} |
                {{ $exam->duration }} minutes
            </p>

            <a href="{{ route('student.exams.start', $exam) }}"
               class="btn btn-primary">
                Start Now
            </a>
        </div>
    </div>
@endforeach

</div>

{{ $exams->links() }}

@endsection
@extends('layouts.app')

@section('heading', 'Examination Result')

@section('content')

<div class="card border-0 shadow-sm p-4 mb-4">

    <h4>{{ $attempt->exam->title }}</h4>

    <h1 class="text-primary mt-3">
        {{ number_format($attempt->obtained_marks, 2) }}
    </h1>

    <p class="text-muted">Total marks obtained</p>

    <div class="row mt-3">
        <div class="col-md-3">
            <h5 class="text-success">
                {{ $attempt->correct_answers }}
            </h5>
            Correct
        </div>

        <div class="col-md-3">
            <h5 class="text-danger">
                {{ $attempt->wrong_answers }}
            </h5>
            Incorrect
        </div>

        <div class="col-md-3">
            <h5>
                {{ $attempt->unanswered }}
            </h5>
            Unanswered
        </div>
    </div>
</div>

<h5>Answer Review</h5>

@foreach($attempt->answers as $answer)

    @php $question = $answer->question; @endphp

    <div class="card border-0 shadow-sm p-3 mb-3">

        <h6>{{ $question->question }}</h6>

        <p>
            Your answer:
            {{ $answer->selected_option ?? 'Not answered' }}
        </p>

        <p class="text-success">
            Correct answer: {{ $question->correct_option }}
        </p>

        @if($attempt->exam->show_explanations)
            <div class="alert alert-info mb-0">
                <strong>Explanation:</strong>
                {{ $question->explanation ?: 'No explanation available.' }}
            </div>
        @endif

        <p class="mt-2 mb-0">
            Marks:
            {{ number_format($answer->marks_obtained, 2) }}
        </p>

    </div>

@endforeach

<a href="{{ route('student.results.index') }}"
   class="btn btn-primary">
    My Results
</a>

@endsection

@extends('admin.layouts.app')

@section('title', 'Import Preview')

@section('content')

<div class="container">

    <h3 class="mb-4">Question Import Preview</h3>

    <div class="row mb-4">

        <div class="col-md-3">
            <div class="card text-center p-3">
                <h4>{{ $batch->total_rows }}</h4>
                Total Questions
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-center p-3">
                <h4 class="text-success">{{ $batch->valid_rows }}</h4>
                Valid Questions
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-center p-3">
                <h4 class="text-danger">{{ $batch->invalid_rows }}</h4>
                Invalid Questions
            </div>
        </div>

        <div class="col-md-3">
            <div class="card text-center p-3">
                <h4 class="text-warning">{{ $batch->duplicate_rows }}</h4>
                Duplicates
            </div>
        </div>

    </div>

    <div class="alert alert-warning">
        Only valid questions will be imported.
        Invalid and duplicate questions will be skipped.
        Review the preview carefully before confirming.
    </div>

    <div class="card shadow-sm">

        <div class="table-responsive">

            <table class="table table-bordered table-hover">

                <thead class="table-dark">
                    <tr>
                        <th>Row</th>
                        <th>Question</th>
                        <th>Options</th>
                        <th>Correct Answer</th>
                        <th>Status</th>
                        <th>Validation Errors</th>
                    </tr>
                </thead>

                <tbody>

                @foreach($batch->preview_data as $row)

                    <tr>

                        <td>{{ $row['row_number'] }}</td>

                        <td>
                            {{ $row['question_text'] }}

                            <br>

                            <small class="text-muted">
                                Marks: {{ $row['marks'] }}
                                | Negative: {{ $row['negative_marks'] }}
                            </small>
                        </td>

                        <td>
                            @foreach($row['options'] as $label => $option)

                                <div>
                                    <strong>{{ $label }}.</strong>
                                    {{ $option }}
                                </div>

                            @endforeach
                        </td>

                        <td>
                            <strong class="text-success">
                                {{ implode(', ', $row['correct_answers']) }}
                            </strong>
                        </td>

                        <td>
                            @if($row['valid'])
                                <span class="badge bg-success">Valid</span>
                            @else
                                <span class="badge bg-danger">Invalid</span>
                            @endif
                        </td>

                        <td>
                            @foreach($row['errors'] as $error)
                                <div class="text-danger small">
                                    {{ $error }}
                                </div>
                            @endforeach

                            @if(empty($row['errors']))
                                <span class="text-success">No errors</span>
                            @endif
                        </td>

                    </tr>

                @endforeach

                </tbody>

            </table>

        </div>

    </div>

    <div class="mt-4 d-flex gap-2">

        @if($batch->valid_rows > 0)

            <form method="POST"
                  action="{{ route('admin.questions.import.confirm', $batch) }}">

                @csrf

                <button type="submit"
                        class="btn btn-success"
                        onclick="return confirm('Are you sure you want to import all valid questions?')">

                    Confirm and Import
                    ({{ $batch->valid_rows }})

                </button>

            </form>

        @endif

        <form method="POST"
              action="{{ route('admin.questions.import.cancel', $batch) }}">

            @csrf
            @method('DELETE')

            <button class="btn btn-danger"
                    onclick="return confirm('Cancel this import?')">

                Cancel Import

            </button>

        </form>

        <a href="{{ route('admin.questions.import') }}"
           class="btn btn-secondary">

            Back

        </a>

    </div>

</div>

@endsection

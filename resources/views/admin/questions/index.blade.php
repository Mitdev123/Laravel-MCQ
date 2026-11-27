@extends('layouts.app')

@section('heading', 'Question Management')

@section('content')

<a href="{{ route('admin.questions.create') }}"
   class="btn btn-primary mb-3">Add Question</a>

<a href="{{ route('admin.questions.import') }}"
   class="btn btn-success mb-3">Import Excel/CSV</a>

<div class="card p-3">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>Question</th>
                <th>Subject</th>
                <th>Category</th>
                <th>Marks</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        @foreach($questions as $question)
            <tr>
                <td>{{ Str::limit($question->question, 70) }}</td>
                <td>{{ $question->subject->name }}</td>
                <td>{{ $question->category?->name }}</td>
                <td>{{ $question->marks }}</td>
                <td>
                    <a href="{{ route('admin.questions.edit', $question) }}"
                       class="btn btn-sm btn-warning">Edit</a>

                    <form method="POST"
                          action="{{ route('admin.questions.destroy', $question) }}"
                          class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger"
                                onclick="return confirm('Delete this question?')">
                            Delete
                        </button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{ $questions->links() }}
</div>

@endsection